<?php

namespace App\Filament\Resources\Webhooks\Pages;

use App\Filament\Resources\Webhooks\Schemas\WebhookForm;
use App\Filament\Resources\Webhooks\WebhookResource;
use App\Models\Webhook;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWebhook extends EditRecord
{
    protected static string $resource = WebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            WebhookResource::testAction(),
            WebhookResource::configureDeleteAction(DeleteAction::make()),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return trans('admin/webhooks.label');
    }

    public function getContentTabIcon(): string
    {
        return 'tabler-webhook';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Webhook $webhook */
        $webhook = $this->record;

        return WebhookForm::mutateBeforeFill([...$data, ...$webhook->only(['endpoint', 'secret', 'headers'])]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return WebhookForm::mutateBeforeSave($data);
    }

    protected function afterSave(): void
    {
        /** @var Webhook $webhook */
        $webhook = $this->record;

        WebhookResource::logActivity($webhook, 'webhook:update');
    }
}
