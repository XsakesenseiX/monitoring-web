<?php

namespace App\Filament\Widgets;

use App\Models\Monitor;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MonitorStatus extends TableWidget
{
    protected static ?string $heading = 'Monitor Status';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Monitor::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Monitor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('health')
                    ->label('Status')
                    ->state(function (Monitor $record): bool {
                        return $record->results()
                            ->latest('checked_at')
                            ->value('is_up') ?? false;
                    })
                    ->boolean(),

                TextColumn::make('status_code')
                    ->label('HTTP')
                    ->state(function (Monitor $record): string {
                        $statusCode = $record->results()
                            ->latest('checked_at')
                            ->value('status_code');

                        return $statusCode !== null
                            ? (string) $statusCode
                            : '—';
                    }),

                TextColumn::make('response_time')
                    ->label('Response')
                    ->state(function (Monitor $record): string {
                        $responseTime = $record->results()
                            ->latest('checked_at')
                            ->value('response_time_ms');

                        return $responseTime !== null
                            ? "{$responseTime} ms"
                            : '—';
                    }),

                TextColumn::make('last_checked')
                    ->label('Last Checked')
                    ->state(function (Monitor $record) {
                        return $record->results()
                            ->latest('checked_at')
                            ->value('checked_at');
                    })
                    ->dateTime(),

                TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->tooltip(fn (Monitor $record): string => $record->url),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated(false);
    }
}