<?php

namespace App\Filament\Resources\Webhooks\RelationManagers;

use App\Filament\Concerns\AuthorizesAdminRelation;
use App\Jobs\Webhooks\DeliverWebhook;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\Acl\RolePermission;
use App\Services\Webhooks\WebhookEvents;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Phiki\Grammar\Grammar;

class DeliveriesRelationManager extends RelationManager
{
    use AuthorizesAdminRelation;

    protected static string $permissionResource = RolePermission::RESOURCE_WEBHOOKS;

    protected static string $relationship = 'deliveries';

    protected static string|\BackedEnum|null $icon = 'tabler-history';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('admin/webhooks.deliveries.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event')
            ->modelLabel(trans('admin/webhooks.deliveries.label'))
            ->pluralModelLabel(trans('admin/webhooks.deliveries.plural_label'))
            ->description(trans('admin/webhooks.deliveries.description', ['days' => config('webhooks.prune_days') ?: '∞']))
            ->emptyStateHeading(trans('admin/webhooks.deliveries.empty'))
            ->emptyStateDescription(trans('admin/webhooks.deliveries.empty_description'))
            ->emptyStateIcon('tabler-history')
            ->defaultSort('id', 'desc')
            ->poll('10s')
            ->columns([
                IconColumn::make('successful')
                    ->label(trans('admin/webhooks.deliveries.columns.status'))
                    ->boolean()
                    ->trueIcon('tabler-circle-check')
                    ->falseIcon('tabler-circle-x'),

                TextColumn::make('event')
                    ->label(trans('admin/webhooks.deliveries.columns.event'))
                    ->formatStateUsing(fn (string $state): string => WebhookEvents::label($state))
                    ->description(fn (WebhookDelivery $record): string => $record->event)
                    ->searchable(),

                TextColumn::make('status_code')
                    ->label(trans('admin/webhooks.deliveries.columns.response'))
                    ->badge()
                    ->placeholder(trans('admin/webhooks.deliveries.no_response'))
                    ->color(fn (WebhookDelivery $record): string => $record->successful ? 'success' : 'danger'),

                TextColumn::make('attempts')
                    ->label(trans('admin/webhooks.deliveries.columns.attempts'))
                    ->numeric(),

                TextColumn::make('duration_ms')
                    ->label(trans('admin/webhooks.deliveries.columns.duration'))
                    ->suffix(' ms')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label(trans('admin/webhooks.deliveries.columns.sent'))
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('successful')
                    ->label(trans('admin/webhooks.deliveries.columns.status'))
                    ->trueLabel(trans('admin/webhooks.statuses.delivered'))
                    ->falseLabel(trans('admin/webhooks.statuses.failed')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn (WebhookDelivery $record): string => WebhookEvents::label($record->event))
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('uuid')
                                ->label(trans('admin/webhooks.deliveries.columns.id'))
                                ->copyable(),

                            TextEntry::make('status_code')
                                ->label(trans('admin/webhooks.deliveries.columns.response'))
                                ->badge()
                                ->placeholder(trans('admin/webhooks.deliveries.no_response'))
                                ->color(fn (WebhookDelivery $record): string => $record->successful ? 'success' : 'danger'),

                            TextEntry::make('updated_at')
                                ->label(trans('admin/webhooks.deliveries.columns.sent'))
                                ->dateTime(),
                        ]),

                        CodeEntry::make('payload')
                            ->label(trans('admin/webhooks.deliveries.columns.payload'))
                            ->state(fn (WebhookDelivery $record): string => (string) json_encode(
                                $record->payload,
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                            ))
                            ->grammar(Grammar::Json)
                            ->copyable(),

                        TextEntry::make('response')
                            ->label(trans('admin/webhooks.deliveries.columns.body'))
                            ->placeholder(trans('admin/webhooks.deliveries.no_body'))
                            ->fontFamily('mono'),
                    ]),

                Action::make('redeliver')
                    ->label(trans('admin/webhooks.actions.redeliver'))
                    ->icon('tabler-refresh')
                    ->color('gray')
                    ->authorize(fn (): bool => static::allows(RolePermission::UPDATE))
                    ->disabled(fn (): bool => ! $this->webhook()->enabled)
                    ->tooltip(fn (): ?string => $this->webhook()->enabled ? null : trans('admin/webhooks.deliveries.disabled'))
                    ->action(function (WebhookDelivery $record): void {
                        // Sent as a delivery of its own so that what happened the first time stays on record.
                        DeliverWebhook::dispatch($record->webhook_id, $record->event, $record->payload);

                        Notification::make()
                            ->title(trans('admin/webhooks.notices.redelivery_queued'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    private function webhook(): Webhook
    {
        /** @var Webhook $webhook */
        $webhook = $this->getOwnerRecord();

        return $webhook;
    }
}
