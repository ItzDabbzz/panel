<?php

namespace App\Filament\Resources\Roles;

use App\Filament\AdminAccess;
use App\Filament\Concerns\AuthorizesAdminResource;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Role;
use App\Services\Acl\RolePermission;
use App\Services\Activity\ActivityLogService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class RoleResource extends Resource
{
    use AuthorizesAdminResource {
        getAuthorizationResponse as getPermissionAuthorizationResponse;
    }

    /**
     * The form keeps one checklist per area of the admin panel under this key.
     */
    public const FORM_KEY = 'access';

    /**
     * How the areas are grouped on the form, mirroring the sidebar.
     */
    private const GROUPS = [
        'management' => [
            RolePermission::RESOURCE_SERVERS,
            RolePermission::RESOURCE_USERS,
            RolePermission::RESOURCE_ROLES,
            RolePermission::RESOURCE_NODES,
            RolePermission::RESOURCE_LOCATIONS,
            RolePermission::RESOURCE_DATABASE_HOSTS,
        ],
        'service' => [
            RolePermission::RESOURCE_NESTS,
            RolePermission::RESOURCE_EGGS,
            RolePermission::RESOURCE_MOUNTS,
            RolePermission::RESOURCE_EXTENSIONS,
            RolePermission::RESOURCE_UPDATES,
        ],
        'administration' => [
            RolePermission::RESOURCE_ALERTS,
            RolePermission::RESOURCE_ACTIVITY_LOG,
            RolePermission::RESOURCE_MONITORING,
            RolePermission::RESOURCE_SETTINGS,
            RolePermission::RESOURCE_DESIGNIFY,
        ],
    ];

    protected static string $permissionResource = RolePermission::RESOURCE_ROLES;

    protected static ?string $model = Role::class;

    protected static ?string $slug = 'roles';

    protected static ?int $navigationSort = 6;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-shield';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-shield-filled';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.management.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.management.roles');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/roles.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/roles.plural-label');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Role::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getAuthorizationResponse(string|\UnitEnum $action, ?Model $record = null): Response
    {
        $response = static::getPermissionAuthorizationResponse($action, $record);
        if ($response->denied() || ! $record instanceof Role) {
            return $response;
        }

        if (in_array(AdminAccess::actionFor($action), [RolePermission::UPDATE, RolePermission::DELETE], true)
            && ($record->root || ! AdminAccess::user()?->canGrantRole($record))) {
            return Response::deny();
        }

        return $response;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(trans('admin/roles.details.title'))
                ->description(trans('admin/roles.details.description'))
                ->icon('tabler-shield')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(trans('admin/roles.details.name'))
                        ->placeholder(trans('admin/roles.details.name_placeholder'))
                        ->required()
                        ->maxLength(191)
                        ->unique(ignoreRecord: true),

                    TextInput::make('description')
                        ->label(trans('admin/roles.details.description_label'))
                        ->placeholder(trans('admin/roles.details.description_placeholder'))
                        ->maxLength(191),
                ]),

            ...array_map(fn (string $group): Section => self::groupSection($group), array_keys(self::GROUPS)),
        ]);
    }

    public static function table(Table $table): Table
    {
        $total = count(RolePermission::all());

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('admin/roles.table.name'))
                    ->description(fn (Role $record): ?string => $record->description)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('permissions')
                    ->label(trans('admin/roles.table.permissions'))
                    ->state(fn (Role $record): string => $record->root
                        ? trans('admin/roles.all')
                        : count($record->permissions).' / '.$total)
                    ->badge()
                    ->color(fn (Role $record): string => $record->root ? 'warning' : 'gray'),

                TextColumn::make('users_count')
                    ->label(trans('admin/roles.table.users'))
                    ->counts('users')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(trans('admin/roles.table.updated'))
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                static::configureDeleteAction(DeleteAction::make()),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->orderByDesc('root'))
            ->defaultSort('name');
    }

    public static function configureDeleteAction(DeleteAction $action): DeleteAction
    {
        return $action
            ->disabled(fn (Role $record): bool => $record->users()->exists())
            ->tooltip(fn (Role $record): ?string => $record->users()->exists() ? trans('admin/roles.messages.in_use') : null)
            ->before(function (Role $record, DeleteAction $action): void {
                if ($record->users()->exists()) {
                    $action->halt();
                }
            })
            ->after(function (Role $record): void {
                app(ActivityLogService::class)
                    ->subject($record)
                    ->event('role:delete')
                    ->property('name', $record->name)
                    ->log();
            });
    }

    /**
     * Splits a stored permission list into the per-area checklists used by the form.
     *
     * @param  string[]  $permissions
     * @return array<string, string[]>
     */
    public static function toFormState(array $permissions): array
    {
        $state = [];
        foreach (RolePermission::groups() as $resource => $actions) {
            $state[$resource] = array_values(array_filter(
                $actions,
                fn (string $action): bool => in_array(RolePermission::key($resource, $action), $permissions, true),
            ));
        }

        return $state;
    }

    /**
     * Turns the per-area checklists back into a permission list. Permissions the current
     * user does not hold are dropped so a role can never grant more than its author has.
     *
     * @return string[]
     */
    public static function fromFormState(array $state): array
    {
        $permissions = [];
        foreach ($state as $resource => $actions) {
            foreach (Arr::wrap($actions) as $action) {
                $permissions[] = RolePermission::key((string) $resource, (string) $action);
            }
        }

        return array_values(array_intersect(
            RolePermission::filter($permissions),
            AdminAccess::user()?->rolePermissions() ?? [],
        ));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    private static function groupSection(string $group): Section
    {
        return Section::make(trans("admin/roles.groups.$group.title"))
            ->description(trans("admin/roles.groups.$group.description"))
            ->columns(['md' => 2, 'xl' => 3])
            ->columnSpanFull()
            ->headerActions([self::groupToggleAction($group)])
            ->schema(array_map(
                fn (string $resource): CheckboxList => self::permissionChecklist($resource),
                self::GROUPS[$group],
            ));
    }

    private static function groupToggleAction(string $group): Action
    {
        $grantable = fn (string $resource): array => array_values(array_filter(
            RolePermission::groups()[$resource],
            fn (string $action): bool => AdminAccess::allows($resource, $action),
        ));

        $complete = function (Get $get) use ($group, $grantable): bool {
            foreach (self::GROUPS[$group] as $resource) {
                if (array_diff($grantable($resource), $get(self::FORM_KEY.'.'.$resource) ?? []) !== []) {
                    return false;
                }
            }

            return true;
        };

        return Action::make('toggle_'.$group)
            ->label(fn (Get $get): string => trans($complete($get) ? 'admin/roles.deselect_all' : 'admin/roles.select_all'))
            ->icon(fn (Get $get): string => $complete($get) ? 'tabler-square-x' : 'tabler-checks')
            ->color('gray')
            ->size('sm')
            ->action(function (Get $get, Set $set) use ($group, $grantable, $complete): void {
                $clear = $complete($get);
                foreach (self::GROUPS[$group] as $resource) {
                    $set(self::FORM_KEY.'.'.$resource, $clear ? [] : $grantable($resource));
                }
            });
    }

    private static function permissionChecklist(string $resource): CheckboxList
    {
        $actions = RolePermission::groups()[$resource];

        return CheckboxList::make(self::FORM_KEY.'.'.$resource)
            ->label(trans("admin/roles.resources.$resource"))
            ->options(array_combine($actions, array_map(
                fn (string $action): string => self::actionLabel($resource, $action),
                $actions,
            )))
            ->disableOptionWhen(fn (string $value): bool => ! AdminAccess::allows($resource, $value))
            ->bulkToggleable(count($actions) > 1)
            ->columns(2)
            ->gridDirection(GridDirection::Row);
    }

    private static function actionLabel(string $resource, string $action): string
    {
        $key = match (true) {
            $action === RolePermission::UPDATE && count(RolePermission::groups()[$resource]) === 1 => 'manage',
            $action === RolePermission::UPDATE && $resource === RolePermission::RESOURCE_UPDATES => 'install',
            default => $action,
        };

        return trans("admin/roles.actions.$key");
    }
}
