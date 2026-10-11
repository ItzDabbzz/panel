<?php

namespace App\Filament\Concerns;

use App\Filament\AdminAccess;
use App\Services\Acl\RolePermission;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesAdminRelation
{
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return AdminAccess::allows(static::$permissionResource);
    }

    public function getAuthorizationResponse(string $action, ?Model $record = null): Response
    {
        $required = AdminAccess::actionFor($action);
        if ($required !== null && $required !== RolePermission::READ && static::changesOwner()) {
            $required = RolePermission::UPDATE;
        }

        return $required !== null && AdminAccess::allows(static::$permissionResource, $required)
            ? Response::allow()
            : Response::deny();
    }

    protected static function changesOwner(): bool
    {
        return false;
    }

    protected static function allows(string $action): bool
    {
        return AdminAccess::allows(static::$permissionResource, $action);
    }
}
