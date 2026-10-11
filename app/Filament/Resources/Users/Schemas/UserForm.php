<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Filament\AdminAccess;
use App\Models\Role;
use App\Models\User;
use App\Services\Acl\RolePermission;
use App\Services\Activity\ActivityLogService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public const ROLES_FIELD = 'role_ids';

    public static function syncRoles(User $user, ?array $selected): void
    {
        $actor = AdminAccess::user();
        if ($selected === null || $actor === null || $user->is($actor)
            || ! $actor->hasRolePermission(RolePermission::RESOURCE_ROLES, RolePermission::ASSIGN)) {
            return;
        }

        $current = $user->roles()->get();
        $grantable = Role::query()->get()->filter(fn (Role $role): bool => $actor->canGrantRole($role));

        $kept = $current->reject(fn (Role $role): bool => $grantable->contains($role));
        $chosen = $grantable->whereIn('id', array_map(intval(...), $selected));
        $roles = $kept->merge($chosen);

        $user->forceFill(['root_admin' => $roles->contains('root', true)])->saveOrFail();

        $changes = $user->roles()->sync($roles->modelKeys());
        $user->unsetRelation('roles');
        if ($changes['attached'] === [] && $changes['detached'] === []) {
            return;
        }

        app(ActivityLogService::class)
            ->subject($user)
            ->event('user:roles-update')
            ->property([
                'old' => $current->pluck('name')->all(),
                'new' => $roles->pluck('name')->all(),
            ])
            ->log();
    }

    public static function withoutPrivileges(array $data): array
    {
        unset($data[self::ROLES_FIELD], $data['root_admin']);

        return $data;
    }

    private static function editingSelf(?User $record): bool
    {
        return (bool) $record?->is(AdminAccess::user());
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make(trans('admin/user.details.account_details'))
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('email')
                            ->label(trans('admin/user.details.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        TextInput::make('username')
                            ->label(trans('admin/user.details.username'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        TextInput::make('name_first')
                            ->label(trans('admin/user.details.first_name'))
                            ->required()
                            ->maxLength(191),
                        TextInput::make('name_last')
                            ->label(trans('admin/user.details.last_name'))
                            ->required()
                            ->maxLength(191),
                        TextInput::make('external_id')
                            ->label(trans('admin/user.details.external_id'))
                            ->nullable()
                            ->maxLength(191),
                        Select::make('language')
                            ->label(trans('admin/user.details.language'))
                            ->options(array_merge(
                                ['geo' => trans('admin/user.details.geolocate')],
                                (new User())->getAvailableLanguages(true)
                            ))
                            ->default(fn () => app(SettingsRepositoryInterface::class)->get('settings::app:locale', 'en'))
                            ->dehydrateStateUsing(fn ($state) => $state ?: app(SettingsRepositoryInterface::class)->get('settings::app:locale', 'en')),
                    ])
                    ->columns(2)
                    ->columnSpan(6),

                Group::make()
                    ->schema([
                        Section::make(trans('admin/user.details.password'))
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                TextInput::make('password')
                                    ->label(trans('admin/user.details.password'))
                                    ->password()
                                    ->confirmed()
                                    ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                                    ->dehydrated(fn ($state) => filled($state)),
                                TextInput::make('password_confirmation')
                                    ->label(trans('admin/user.details.password_confirmation'))
                                    ->password()
                                    ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                                    ->dehydrated(false),
                            ]),

                        Section::make(trans('admin/user.details.privileges'))
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Select::make(self::ROLES_FIELD)
                                    ->label(trans('admin/roles.user.roles'))
                                    ->helperText(fn (?User $record): ?string => self::editingSelf($record)
                                        ? trans('admin/roles.user.roles_self')
                                        : null)
                                    ->placeholder(trans('admin/roles.user.none'))
                                    ->multiple()
                                    ->options(fn (): array => Role::query()->orderByDesc('root')->orderBy('name')->pluck('name', 'id')->all())
                                    ->disableOptionWhen(fn (string $value): bool => ! AdminAccess::user()?->canGrantRole(Role::query()->findOrFail($value)))
                                    ->disabled(fn (?User $record): bool => self::editingSelf($record)
                                        || ! AdminAccess::allows(RolePermission::RESOURCE_ROLES, RolePermission::ASSIGN)),
                            ]),
                    ])
                    ->columnSpan(6),
            ]);
    }
}
