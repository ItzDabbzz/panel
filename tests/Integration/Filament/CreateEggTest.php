<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Nests\Eggs\Pages\CreateEgg;
use App\Filament\Resources\Nests\Eggs\Pages\EditEgg;
use App\Models\Egg;
use App\Models\Nest;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class CreateEggTest extends IntegrationTestCase
{
    public function test_nest_from_create_link_is_preselected_and_can_be_changed(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $originalNest = Nest::factory()->create();
        $selectedNest = Nest::factory()->create();

        foreach ([$originalNest, $selectedNest] as $nest) {
            $name = 'Egg for nest '.$nest->id;

            Livewire::withQueryParams(['nest_id' => $originalNest->id])
                ->test(CreateEgg::class)
                ->assertFormSet(['nest_id' => $originalNest->id])
                ->fillForm([
                    'nest_id' => $nest->id,
                    'name' => $name,
                    'docker_images' => ['Demo' => 'alpine:3.20'],
                    'startup' => 'echo demo',
                    'config_stop' => 'stop',
                    'config_startup' => '{"done":"ready"}',
                    'config_logs' => '{}',
                    'config_files' => '{}',
                    'script_container' => 'alpine:3.20',
                    'script_entry' => 'sh',
                    'script_install' => '#!/bin/sh',
                ])
                ->call('create')
                ->assertHasNoFormErrors()
                ->assertRedirect();

            $egg = Egg::query()->where('name', $name)->sole();
            $this->assertSame($nest->id, $egg->nest_id);
            $this->assertSame('Created', $egg->creation_source_label);
        }
    }

    public function test_editor_rejects_duplicate_environment_names(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $server = $this->createServerModel();
        $variable = [
            'name' => 'Test option', 'env_variable' => 'TEST_OPTION',
            'default_value' => 'value', 'rules' => ['required', 'string'],
            'user_viewable' => true, 'user_editable' => true,
        ];

        Livewire::test(EditEgg::class, ['record' => $server->egg_id])
            ->fillForm(['variables' => ['first' => $variable, 'second' => $variable]])
            ->call('save')
            ->assertHasFormErrors(['variables.first.env_variable', 'variables.second.env_variable']);

        $this->assertDatabaseMissing('egg_variables', ['egg_id' => $server->egg_id, 'env_variable' => 'TEST_OPTION']);
    }

    public function test_nest_is_required_without_a_create_link_default(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateEgg::class)
            ->assertFormSet(['nest_id' => null])
            ->call('create')
            ->assertHasFormErrors(['nest_id' => 'required']);

        $this->assertDatabaseCount('eggs', 0);
    }
}
