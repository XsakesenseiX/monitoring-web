<?php

namespace App\Filament\Resources\Monitors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class NotificationSettingsRelationManager extends RelationManager
{
    protected static string $relationship = 'notificationSettings';

    protected static ?string $title = 'Notification Settings';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('channel')
            ->columns([
                TextColumn::make('event')
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title())
                    ->sortable(),

                TextColumn::make('channel')
                    ->formatStateUsing(fn (string $state): string => str($state)->title())
                    ->sortable(),

                ToggleColumn::make('enabled'),

                TextInputColumn::make('destination')
                    ->placeholder('—')
                    ->visible(fn ($record) => $record?->channel !== 'log'),
            ])
            ->defaultSort('event')
            ->headerActions([
                // Baris dibuat & disinkronkan otomatis oleh MonitorObserver.
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
