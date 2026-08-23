<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentIncidents extends TableWidget
{
    protected static ?string $heading = 'Recent Incidents';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Incident::query()
                    ->with('monitor')
                    ->latest('started_at')
            )
            ->columns([
                TextColumn::make('monitor.name')
                    ->label('Monitor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'resolved' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->dateTime()
                    ->placeholder('Still open'),

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
                    }),

                TextColumn::make('failure_count')
                    ->label('Failures'),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state),
            ])
            ->paginated(false);
    }
}