<?php

namespace Tests\Integration\Filament;

use App\Filament\Pages\Settings;
use App\Filament\Resources\Api\ApiKeyResource;
use App\Filament\Resources\Locations\LocationResource;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Servers\ServerResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Integration\IntegrationTestCase;

class RolesTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_role_holder_only_reaches_what_the_role_grants(): void
    {
        $user = $this->actingAsRoleHolder(['locations.read', 'locations.update']);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse(User::factory()->create()->isAdmin());
        $this->assertTrue($user->toVueObject()['admin']);
        $this->assertFalse($user->toVueObject()['root_admin']);

        $this->get('/admin')->assertOk();
        $this->get(LocationResource::getUrl())->assertOk();
        $this->get(ServerResource::getUrl())->assertForbidden();
        $this->get(Settings::getUrl())->assertForbidden();
        $this->get(ApiKeyResource::getUrl())->assertForbidden();
        $this->getJson('/api/application/users')->assertForbidden();

        $location = Location::factory()->create();
        $this->assertTrue(LocationResource::canEdit($location));
        $this->assertFalse(LocationResource::canCreate());
        $this->assertFalse(LocationResource::canDelete($location));

        Livewire::test(ListLocations::class)
            ->assertTableActionVisible('edit', $location)
            ->assertTableActionHidden('delete', $location);
    }

    public function test_user_without_a_role_cannot_enter_the_admin_area(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin')->assertForbidden();

        $this->actingAsRoleHolder([]);
        $this->get('/admin')->assertForbidden();
    }

    public function test_role_can_only_grant_what_its_author_holds(): void
    {
        $this->actingAsRoleHolder(['roles.read', 'roles.create', 'roles.update', 'locations.read']);

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'Helpers',
                'access.locations' => ['read', 'delete'],
                'access.settings' => ['update'],
            ])
            ->call('create')
            ->assertHasFormErrors(['access.locations.1', 'access.settings.0']);

        $this->assertDatabaseMissing('roles', ['name' => 'Helpers']);
        $this->assertSame(['locations.read'], RoleResource::fromFormState([
            'locations' => ['read', 'delete'],
            'settings' => ['update'],
        ]));

        Livewire::test(CreateRole::class)
            ->fillForm(['name' => 'Helpers', 'access.locations' => ['read']])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::query()->where('name', 'Helpers')->sole();
        $this->assertSame(['locations.read'], $role->permissions);
        $this->assertActivityLogged('role:create');

        $powerful = Role::factory()->create(['permissions' => ['settings.update']]);
        $this->assertFalse(RoleResource::canEdit($powerful));
        $this->assertFalse(RoleResource::canDelete($powerful));
        Livewire::test(EditRole::class, ['record' => $powerful->id])->assertForbidden();
    }

    public function test_role_in_use_cannot_be_deleted(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        $role = Role::factory()->create(['permissions' => ['servers.read']]);
        $role->users()->attach(User::factory()->create());

        Livewire::test(EditRole::class, ['record' => $role->id])->assertActionDisabled('delete');

        $role->users()->detach();

        Livewire::test(EditRole::class, ['record' => $role->id])->callAction('delete');
        $this->assertModelMissing($role);
    }

    public function test_root_administrator_is_a_role_that_cannot_be_changed_or_deleted(): void
    {
        $root = User::factory()->create(['root_admin' => true]);
        $this->actingAs($root);

        $role = Role::root();
        $this->assertTrue($root->roles->contains($role));
        $this->assertFalse(RoleResource::canEdit($role));
        $this->assertFalse(RoleResource::canDelete($role));
        $this->assertFalse($role->delete());
        $this->assertModelExists($role);

        $user = User::factory()->create();
        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm([UserForm::ROLES_FIELD => [$role->id]])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertTrue($user->refresh()->root_admin);

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm([UserForm::ROLES_FIELD => []])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertFalse($user->refresh()->root_admin);
        $this->assertFalse($user->roles()->exists());

        $user->update(['root_admin' => true]);
        $this->assertTrue($user->roles()->where('root', true)->exists());

        Livewire::test(EditUser::class, ['record' => $root->id])->assertFormFieldDisabled(UserForm::ROLES_FIELD);

        Livewire::test(ListUsers::class)
            ->filterTable('roles', [$role->id])
            ->assertCanSeeTableRecords([$root, $user])
            ->assertCanNotSeeTableRecords([User::factory()->create()]);
    }

    public function test_role_holder_cannot_manage_more_powerful_accounts(): void
    {
        $this->actingAsRoleHolder(['users.read', 'users.update', 'users.delete', 'roles.assign']);
        $root = User::factory()->create(['root_admin' => true]);
        $staff = User::factory()->create();
        $staff->roles()->attach(Role::factory()->create(['permissions' => ['settings.update']]));
        $regular = User::factory()->create();

        $this->assertFalse(UserResource::canEdit($root));
        $this->assertFalse(UserResource::canDelete($root));
        $this->assertFalse(UserResource::canEdit($staff));
        $this->assertTrue(UserResource::canEdit($regular));
        Livewire::test(EditUser::class, ['record' => $root->id])->assertForbidden();

        $grantable = Role::factory()->create(['permissions' => ['users.read']]);
        $beyond = Role::factory()->create(['permissions' => ['servers.delete']]);

        foreach ([$beyond, Role::root()] as $forbidden) {
            Livewire::test(EditUser::class, ['record' => $regular->id])
                ->fillForm([UserForm::ROLES_FIELD => [$grantable->id, $forbidden->id]])
                ->call('save');

            $this->assertNotContains($forbidden->id, $regular->roles()->pluck('roles.id')->all());
            $this->assertFalse($regular->refresh()->root_admin);
        }

        Livewire::test(EditUser::class, ['record' => $regular->id])
            ->fillForm([UserForm::ROLES_FIELD => [$grantable->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$grantable->id], $regular->roles()->pluck('roles.id')->all());
        $this->assertActivityLogged('user:roles-update');

        UserForm::syncRoles($regular, [$grantable->id, $beyond->id, Role::root()->id]);
        $this->assertSame([$grantable->id], $regular->roles()->pluck('roles.id')->all());
        $this->assertFalse($regular->refresh()->root_admin);
    }

    public function test_server_permissions_reach_the_client_area(): void
    {
        $server = $this->createServerModel();
        $path = "/api/client/servers/{$server->uuid}";

        $this->actingAsRoleHolder(['locations.read']);
        $this->getJson($path)->assertNotFound();

        $viewer = $this->actingAsRoleHolder(['servers.read']);
        $permissions = $this->getJson($path)->assertOk()->json('meta.user_permissions');
        $this->assertContains(Permission::ACTION_FILE_READ, $permissions);
        $this->assertNotContains(Permission::ACTION_FILE_UPDATE, $permissions);
        $this->assertNotContains('*', $permissions);
        $this->assertTrue($viewer->can(Permission::ACTION_FILE_READ, $server));
        $this->assertFalse($viewer->can(Permission::ACTION_CONTROL_START, $server));
        $this->postJson("$path/power", ['signal' => 'start'])->assertForbidden();
        $this->getJson('/api/client?type=admin')->assertJsonPath('data.0.attributes.uuid', $server->uuid);
    }

    public function test_editing_servers_allows_everything_on_them_in_the_client_area(): void
    {
        $server = $this->createServerModel();
        $path = "/api/client/servers/{$server->uuid}";

        $editor = $this->actingAsRoleHolder(['servers.read', 'servers.update']);
        $this->assertContains(Permission::ACTION_FILE_UPDATE, $this->getJson($path)->assertOk()->json('meta.user_permissions'));
        $this->assertTrue($editor->can(Permission::ACTION_CONTROL_START, $server));
    }

    /**
     * @param  string[]  $permissions
     */
    private function actingAsRoleHolder(array $permissions): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['permissions' => $permissions]));
        $this->actingAs($user);

        return $user;
    }
}
