<?php

namespace App\Filament\Resources\Monitors\Tables;

use App\Models\MonitorResult;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Monitor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->searchable(),

                IconColumn::make('health')
                    ->label('Health')
                    ->state(function ($record): bool {
                        return MonitorResult::query()
                            ->where('monitor_id', $record->id)
                            ->latest('checked_at')
                            ->value('is_up') ?? false;
                    })
                    ->boolean(),

                TextColumn::make('last_status_code')
                    ->label('HTTP')
                    ->state(function ($record) {
                        return MonitorResult::query()
                            ->where('monitor_id', $record->id)
                            ->latest('checked_at')
                            ->value('status_code') ?? '—';
                    }),

                TextColumn::make('response_time')
                    ->label('Response')
                    ->state(function ($record) {
                        $time = MonitorResult::query()
                            ->where('monitor_id', $record->id)
                            ->latest('checked_at')
                            ->value('response_time_ms');

                        return $time !== null ? "{$time} ms" : '—';
                    }),

                TextColumn::make('last_checked')
                    ->label('Last Checked')
                    ->state(function ($record) {
                        return MonitorResult::query()
                            ->where('monitor_id', $record->id)
                            ->latest('checked_at')
                            ->value('checked_at');
                    })
                    ->dateTime(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('interval')
                    ->label('Interval')
                    ->suffix(' sec'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}