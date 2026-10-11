<?php

namespace App\Filament\Resources\Webhooks;

use App\Filament\Concerns\AuthorizesAdminResource;
use App\Filament\Resources\Webhooks\Pages\CreateWebhook;
use App\Filament\Resources\Webhooks\Pages\EditWebhook;
use App\Filament\Resources\Webhooks\Pages\ListWebhooks;
use App\Filament\Resources\Webhooks\RelationManagers\DeliveriesRelationManager;
use App\Filament\Resources\Webhooks\Schemas\WebhookForm;
use App\Filament\Resources\Webhooks\Tables\WebhooksTable;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\Acl\RolePermission;
use App\Services\Activity\ActivityLogService;
use App\Services\Webhooks\WebhookDeliveryService;
use App\Services\Webhooks\WebhookDispatcher;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WebhookResource extends Resource
{
    use AuthorizesAdminResource;

    protected static string $permissionResource = RolePermission::RESOURCE_WEBHOOKS;

    protected static ?string $model = Webhook::class;

    protected static ?int $navigationSort = 4;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-webhook';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.administration.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.administration.webhooks');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/webhooks.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/webhooks.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return WebhookForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebhooksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebhooks::route('/'),
            'create' => CreateWebhook::route('/create'),
            'edit' => EditWebhook::route('/{record}/edit'),
        ];
    }

    public static function testAction(): Action
    {
        return Action::make('test')
            ->label(trans('admin/webhooks.actions.test'))
            ->icon('tabler-send')
            ->color('gray')
            ->authorize(fn (Webhook $record): bool => static::canEdit($record))
            ->action(function (Webhook $record): void {
                static::notifyDelivery(
                    app(WebhookDeliveryService::class)->send($record, app(WebhookDispatcher::class)->sample())
                );
            });
    }

    public static function configureDeleteAction(DeleteAction $action): DeleteAction
    {
        return $action->after(fn (Webhook $record) => static::logActivity($record, 'webhook:delete'));
    }

    public static function notifyDelivery(WebhookDelivery $delivery): void
    {
        $detail = $delivery->status_code !== null
            ? trans('admin/webhooks.notices.responded', ['status' => $delivery->status_code, 'ms' => $delivery->duration_ms])
            : null;

        $notification = Notification::make()
            ->title(trans($delivery->successful ? 'admin/webhooks.notices.delivered' : 'admin/webhooks.notices.failed'))
            ->body(trim($detail.' '.($delivery->successful ? '' : Str::limit((string) $delivery->response, 200))) ?: null);

        ($delivery->successful ? $notification->success() : $notification->danger())->send();
    }

    public static function logActivity(Webhook $webhook, string $event): void
    {
        app(ActivityLogService::class)
            ->subject($webhook)
            ->event($event)
            ->property(['name' => $webhook->name, 'type' => $webhook->type, 'events' => $webhook->events])
            ->log();
    }
}
