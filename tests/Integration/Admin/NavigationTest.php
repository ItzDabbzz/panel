<?php

namespace Tests\Integration\Admin;

use App\Models\User;
use Tests\Integration\IntegrationTestCase;

class NavigationTest extends IntegrationTestCase
{
    public function test_admin_navigation_uses_livewire_except_when_entering_designify(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));

        $this->get('/admin/settings')
            ->assertOk()
            ->assertSee('href="'.url('/admin').'" wire:navigate', false)
            ->assertSee('href="'.url('/admin/designify').'"', false)
            ->assertDontSee('href="'.url('/admin/designify').'" wire:navigate', false);
    }

    public function test_designify_navigation_uses_full_page_loads(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));

        $this->get('/admin/designify')
            ->assertOk()
            ->assertSee('href="'.url('/admin').'"', false)
            ->assertDontSee('href="'.url('/admin').'" wire:navigate', false)
            ->assertDontSee('href="'.url('/admin/settings').'" wire:navigate', false);
    }
}
