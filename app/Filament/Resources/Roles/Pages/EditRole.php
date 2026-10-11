<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Role;
use App\Services\Activity\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RoleResource::configureDeleteAction(DeleteAction::make()),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data[RoleResource::FORM_KEY] = RoleResource::toFormState($data['permissions'] ?? []);
        unset($data['permissions']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['permissions'] = RoleResource::fromFormState($data[RoleResource::FORM_KEY] ?? []);
        unset($data[RoleResource::FORM_KEY]);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Role $role */
        $role = $this->record;

        app(ActivityLogService::class)
            ->subject($role)
            ->event('role:update')
            ->property(['name' => $role->name, 'permissions' => $role->permissions])
            ->log();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
