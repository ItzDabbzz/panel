<?php

namespace Tests\Integration\Models;

use App\Exceptions\DisplayException;
use App\Models\EggVariable;
use App\Services\Eggs\Variables\VariableCreationService;
use Tests\Integration\IntegrationTestCase;

class EggVariableTest extends IntegrationTestCase
{
    public function test_creation_rejects_duplicate_names_within_the_same_egg(): void
    {
        $server = $this->createServerModel();
        $variable = $server->egg->variables->first();
        $data = $variable->only(['name', 'description', 'env_variable', 'default_value', 'rules']);
        $otherEgg = $this->cloneEggAndVariables($server->egg);
        $otherEgg->variables()->delete();
        $created = app(VariableCreationService::class)->handle($otherEgg->id, $data);
        $this->assertSame($variable->env_variable, $created->env_variable);

        $this->expectException(DisplayException::class);
        $this->expectExceptionMessage(trans('exceptions.service.variables.env_not_unique', ['name' => $variable->env_variable]));
        app(VariableCreationService::class)->handle($server->egg_id, $data);
    }

    public function test_egg_relationship(): void
    {
        $server = $this->createServerModel();
        $variable = EggVariable::query()->where('egg_id', $server->egg_id)->firstOrFail();

        $this->assertTrue($variable->egg->is($server->egg));
    }
}
