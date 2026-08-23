<?php

namespace App\Filament\Resources\Incidents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IncidentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('monitor.name')
                    ->label('Monitor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'resolved' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->dateTime()
                    ->placeholder('Still open')
                    ->sortable(),

                TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(function ($state): string {
                        if ($state === null) {
                            return '—';
                        }

                        $seconds = (int) $state;

                        $minutes = intdiv($seconds, 60);
                        $remainingSeconds = $seconds % 60;

                        if ($minutes > 0) {
                            return "{$minutes}m {$remainingSeconds}s";
                        }

                        return "{$remainingSeconds}s";
                    })
                    ->sortable(),

                TextColumn::make('failure_count')
                    ->label('Failures')
                    ->sortable(),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(60)
                    ->tooltip(fn ($state) => $state),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}