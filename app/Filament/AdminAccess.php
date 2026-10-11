<?php

namespace App\Filament;

use App\Models\User;
use App\Services\Acl\RolePermission;
use Filament\Facades\Filament;

class AdminAccess
{
    private const ABILITIES = [
        'viewAny' => RolePermission::READ,
        'view' => RolePermission::READ,
        'create' => RolePermission::CREATE,
        'replicate' => RolePermission::CREATE,
        'update' => RolePermission::UPDATE,
        'reorder' => RolePermission::UPDATE,
        'attach' => RolePermission::UPDATE,
        'detach' => RolePermission::UPDATE,
        'detachAny' => RolePermission::UPDATE,
        'associate' => RolePermission::UPDATE,
        'dissociate' => RolePermission::UPDATE,
        'dissociateAny' => RolePermission::UPDATE,
        'restore' => RolePermission::UPDATE,
        'restoreAny' => RolePermission::UPDATE,
        'delete' => RolePermission::DELETE,
        'deleteAny' => RolePermission::DELETE,
        'forceDelete' => RolePermission::DELETE,
        'forceDeleteAny' => RolePermission::DELETE,
    ];

    public static function user(): ?User
    {
        $user = Filament::auth()->user();

        return $user instanceof User ? $user : null;
    }

    public static function allows(string $resource, string $action = RolePermission::READ): bool
    {
        return (bool) static::user()?->hasRolePermission($resource, $action);
    }

    public static function isRoot(): bool
    {
        return (bool) static::user()?->root_admin;
    }

    public static function actionFor(string|\UnitEnum $ability): ?string
    {
        $ability = match (true) {
            $ability instanceof \BackedEnum => (string) $ability->value,
            $ability instanceof \UnitEnum => $ability->name,
            default => $ability,
        };

        return self::ABILITIES[$ability] ?? null;
    }
}
