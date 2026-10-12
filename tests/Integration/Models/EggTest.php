<?php

namespace Tests\Integration\Models;

use App\Exceptions\Service\InvalidFileUploadException;
use App\Models\Egg;
use App\Models\EggVariable;
use App\Services\Eggs\EggConfigurationService;
use App\Services\Eggs\EggCreationService;
use App\Services\Eggs\Sharing\EggExporterService;
use App\Services\Eggs\Sharing\EggImporterService;
use App\Services\Eggs\Sharing\EggUpdateImporterService;
use App\Services\Servers\ServerConfigurationStructureService;
use Illuminate\Http\UploadedFile;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('importModes')]
    public function test_import_rejects_duplicate_names_without_changing_stored_eggs(bool $update): void
    {
        $egg = $this->createServerModel()->egg;
        $parsed = json_decode(app(EggExporterService::class)->handle($egg->id), true, flags: JSON_THROW_ON_ERROR);
        $parsed['name'] = 'Imported egg';
        $parsed['variables'][] = array_merge($parsed['variables'][0], ['default_value' => 'duplicate']);
        $eggCount = Egg::query()->count();
        $variables = EggVariable::query()->orderBy('id')->get()->toArray();
        $originalName = $egg->name;
        $file = UploadedFile::fake()->createWithContent('egg.json', json_encode($parsed, JSON_THROW_ON_ERROR));

        try {
            if ($update) {
                app(EggUpdateImporterService::class)->handle($egg, $file);
            } else {
                app(EggImporterService::class)->handle($file, $egg->nest_id);
            }
            $this->fail('An import with duplicate environment names must be rejected.');
        } catch (InvalidFileUploadException $exception) {
            $this->assertSame(trans('exceptions.service.variables.env_not_unique', ['name' => $parsed['variables'][0]['env_variable']]), $exception->getMessage());
        }

        $this->assertDatabaseCount('eggs', $eggCount);
        $this->assertSame($originalName, $egg->fresh()->name);
        $this->assertSame($variables, EggVariable::query()->orderBy('id')->get()->toArray());
    }

    #[DataProvider('importModes')]
    public function test_import_accepts_unique_environment_names(bool $update): void
    {
        $egg = $this->createServerModel()->egg;
        $parsed = json_decode(app(EggExporterService::class)->handle($egg->id), true, flags: JSON_THROW_ON_ERROR);
        $parsed['name'] = 'Imported egg';
        $file = UploadedFile::fake()->createWithContent('egg.json', json_encode($parsed, JSON_THROW_ON_ERROR));
        $imported = $update
            ? app(EggUpdateImporterService::class)->handle($egg, $file)
            : app(EggImporterService::class)->handle($file, $egg->nest_id);

        $this->assertSame('Imported egg', $imported->name);
        $this->assertSame(array_column($parsed['variables'], 'env_variable'), $imported->variables()->orderBy('id')->pluck('env_variable')->all());
    }

    public static function importModes(): array
    {
        return [[false], [true]];
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
