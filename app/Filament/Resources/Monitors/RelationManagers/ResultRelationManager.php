<?php

namespace App\Filament\Resources\Monitors\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';

    protected static ?string $title = 'Check History';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('checked_at')
            ->columns([
                IconColumn::make('is_up')
                    ->label('Status')
                    ->boolean(),

                TextColumn::make('status_code')
                    ->label('HTTP')
                    ->placeholder('—'),

                TextColumn::make('response_time_ms')
                    ->label('Response')
                    ->suffix(' ms')
                    ->sortable(),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->placeholder('—')
                    ->limit(50),

                TextColumn::make('checked_at')
                    ->label('Checked At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('checked_at', 'desc')
            ->headerActions([
                // Results dibuat otomatis oleh CheckMonitorJob.
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}