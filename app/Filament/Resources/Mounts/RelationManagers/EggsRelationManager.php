<?php

namespace App\Filament\Resources\Mounts\RelationManagers;

use App\Filament\Concerns\AuthorizesAdminRelation;
use App\Services\Acl\RolePermission;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EggsRelationManager extends RelationManager
{
    use AuthorizesAdminRelation;

    protected static string $permissionResource = RolePermission::RESOURCE_MOUNTS;

    protected static function changesOwner(): bool
    {
        return true;
    }

    protected static string $relationship = 'eggs';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(trans('strings.id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nest.name')
                    ->label(trans('admin/nests.label'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable(),
            ])
            ->filters([

            ])
            ->headerActions([
                AttachAction::make()
                    ->authorize(fn (): bool => static::allows(RolePermission::UPDATE))
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'author'])
                    ->label(trans('admin/mounts.actions.attach_egg')),
            ])
            ->actions([
                DetachAction::make()
                    ->authorize(fn (): bool => static::allows(RolePermission::UPDATE)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->authorize(fn (): bool => static::allows(RolePermission::UPDATE)),
                ]),
            ]);
    }
}
