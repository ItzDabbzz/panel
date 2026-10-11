<?php

namespace App\Filament\Resources\Webhooks\Pages;

use App\Filament\Resources\Webhooks\Schemas\WebhookForm;
use App\Filament\Resources\Webhooks\WebhookResource;
use App\Models\Webhook;
use Filament\Resources\Pages\CreateRecord;

class CreateWebhook extends CreateRecord
{
    protected static string $resource = WebhookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return WebhookForm::mutateBeforeSave($data);
    }

    protected function afterCreate(): void
    {
        /** @var Webhook $webhook */
        $webhook = $this->record;

        WebhookResource::logActivity($webhook, 'webhook:create');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
