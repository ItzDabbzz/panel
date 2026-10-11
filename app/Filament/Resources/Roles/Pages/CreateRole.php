<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Role;
use App\Services\Activity\ActivityLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['permissions'] = RoleResource::fromFormState($data[RoleResource::FORM_KEY] ?? []);
        unset($data[RoleResource::FORM_KEY]);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Role $role */
        $role = $this->record;

        app(ActivityLogService::class)
            ->subject($role)
            ->event('role:create')
            ->property(['name' => $role->name, 'permissions' => $role->permissions])
            ->log();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
