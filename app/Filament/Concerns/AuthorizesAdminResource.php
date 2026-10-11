<?php

namespace App\Filament\Concerns;

use App\Filament\AdminAccess;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesAdminResource
{
    public static function getAuthorizationResponse(string|\UnitEnum $action, ?Model $record = null): Response
    {
        $required = AdminAccess::actionFor($action);

        return $required !== null && AdminAccess::allows(static::$permissionResource, $required)
            ? Response::allow()
            : Response::deny();
    }
}
