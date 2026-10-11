<?php

namespace App\Filament\Resources\Webhooks\Tables;

use App\Filament\Resources\Webhooks\Schemas\WebhookForm;
use App\Filament\Resources\Webhooks\WebhookResource;
use App\Models\Webhook;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class WebhooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('latestDelivery'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(trans('admin/webhooks.columns.name'))
                    ->description(fn (Webhook $record): ?string => $record->description)
                    ->searchable(['name', 'description'])
                    ->sortable(),

                TextColumn::make('type')
                    ->label(trans('admin/webhooks.columns.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => trans("admin/webhooks.types.$state"))
                    ->icon(fn (string $state): string => $state === Webhook::TYPE_DISCORD ? 'tabler-brand-discord' : 'tabler-webhook')
                    ->color(fn (string $state): string => $state === Webhook::TYPE_DISCORD ? 'info' : 'gray'),

                TextColumn::make('endpoint')
                    ->label(trans('admin/webhooks.columns.endpoint'))
                    ->state(fn (Webhook $record): string => $record->endpointHost()),

                TextColumn::make('events')
                    ->label(trans('admin/webhooks.columns.events'))
                    ->state(fn (Webhook $record): int => count($record->events ?? []))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('latestDelivery.successful')
                    ->label(trans('admin/webhooks.columns.last_delivery'))
                    ->badge()
                    ->placeholder(trans('admin/webhooks.statuses.never'))
                    ->formatStateUsing(fn (bool $state): string => trans($state ? 'admin/webhooks.statuses.delivered' : 'admin/webhooks.statuses.failed'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->description(fn (Webhook $record): ?string => $record->latestDelivery?->created_at?->diffForHumans()),

                ToggleColumn::make('enabled')
                    ->label(trans('admin/webhooks.columns.enabled'))
                    ->disabled(fn (Webhook $record): bool => ! WebhookResource::canEdit($record)),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(trans('admin/webhooks.columns.type'))
                    ->options(WebhookForm::typeOptions()),

                TernaryFilter::make('enabled')
                    ->label(trans('admin/webhooks.columns.enabled')),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    WebhookResource::testAction(),

                    ReplicateAction::make()
                        ->label(trans('admin/webhooks.actions.duplicate'))
                        ->excludeAttributes(['uuid'])
                        ->beforeReplicaSaved(function (Webhook $replica): void {
                            $replica->name = Str::limit(trans('admin/webhooks.copy_suffix', ['name' => $replica->name]), 191, '');
                            $replica->enabled = false;
                        })
                        ->after(fn (Webhook $replica) => WebhookResource::logActivity($replica, 'webhook:create'))
                        ->successNotificationTitle(trans('admin/webhooks.notices.duplicated')),

                    WebhookResource::configureDeleteAction(DeleteAction::make()),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function (Collection $records): void {
                            foreach ($records as $record) {
                                if (! $record instanceof Webhook || $record->exists) {
                                    continue;
                                }

                                WebhookResource::logActivity($record, 'webhook:delete');
                            }
                        }),
                ]),
            ]);
    }
}
