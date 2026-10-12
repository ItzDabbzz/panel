<?php

namespace Tests\Integration\Models;

use App\Models\Egg;
use App\Services\Eggs\EggConfigurationService;
use App\Services\Eggs\EggCreationService;
use App\Services\Servers\ServerConfigurationStructureService;
use LogicException;
use Tests\Integration\IntegrationTestCase;

class EggTest extends IntegrationTestCase
{
    public function test_configuration_resolves_through_inheritance_chains(): void
    {
        $server = $this->createServerModel();
        $parent = $server->egg;
        $parent->forceFill([
            'config_files' => '{"server.properties":{"parser":"properties","find":{"port":"{{server.build.default.port}}"}}}',
            'config_startup' => '{"done":"ready"}',
            'config_logs' => '{"custom":true}',
            'config_stop' => '^SIGTERM',
            'features' => ['eula'],
            'file_denylist' => ['secret.txt'],
        ])->save();

        $child = $this->createInheritedEgg($parent);
        $grandchild = $this->createInheritedEgg($child);
        $server->forceFill(['egg_id' => $grandchild->id])->save();
        $server->refresh();

        foreach (['config_files', 'config_startup', 'config_logs', 'config_stop', 'features', 'file_denylist'] as $field) {
            $this->assertNull($grandchild->{$field});
            $this->assertSame($parent->{$field}, $grandchild->{'inherit_'.$field});
        }

        $configuration = $this->app->make(EggConfigurationService::class)->handle($server);
        $this->assertSame(['ready'], $configuration['startup']['done']);
        $this->assertSame(['type' => 'signal', 'value' => 'SIGTERM'], $configuration['stop']);
        $this->assertSame('server.properties', $configuration['configs'][0]['file']);
        $this->assertSame((string) $server->allocation->port, $configuration['configs'][0]['replace'][0]['replace_with']);
        $this->assertSame(['secret.txt'], $this->app->make(ServerConfigurationStructureService::class)->handle($server)['egg']['file_denylist']);
    }

    public function test_nearest_configuration_overrides_are_preserved(): void
    {
        $parent = $this->createServerModel()->egg;
        $child = $this->createInheritedEgg($parent);
        $child->forceFill([
            'config_files' => '{}',
            'config_startup' => '{"done":"child ready"}',
            'config_logs' => '{}',
            'config_stop' => 'quit',
            'features' => [],
            'file_denylist' => ['ignored-child.txt'],
        ])->save();
        $grandchild = $this->createInheritedEgg($child);

        foreach (['config_files', 'config_startup', 'config_logs', 'config_stop', 'features'] as $field) {
            $this->assertSame($child->{$field}, $grandchild->{'inherit_'.$field});
        }

        $grandchild->config_stop = 'exit';
        $this->assertSame('exit', $grandchild->inherit_config_stop);
        $this->assertSame($parent->file_denylist, $grandchild->inherit_file_denylist);
    }

    public function test_circular_inheritance_fails_without_unbounded_traversal(): void
    {
        $parent = $this->createServerModel()->egg;
        $child = $this->createInheritedEgg($parent);
        $parent->forceFill(['config_from' => $child->id, 'config_files' => null])->save();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Egg configuration inheritance contains a cycle.');

        $child->inherit_config_files;
    }

    private function createInheritedEgg(Egg $parent): Egg
    {
        return $this->app->make(EggCreationService::class)->handle([
            'nest_id' => $parent->nest_id,
            'name' => 'Inherited '.$parent->id,
            'docker_images' => $parent->docker_images,
            'startup' => $parent->startup,
            'config_from' => $parent->id,
        ]);
    }
}
