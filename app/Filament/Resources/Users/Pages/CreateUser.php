<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource;
use App\Services\Users\UserCreationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = app(UserCreationService::class)->handle(UserForm::withoutPrivileges($data));

        UserForm::syncRoles($user, $data[UserForm::ROLES_FIELD] ?? null);

        return $user;
    }
}
