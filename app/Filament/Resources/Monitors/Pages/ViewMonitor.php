<?php

namespace App\Filament\Resources\Monitors\Pages;

use App\Filament\Resources\Monitors\MonitorResource;
use App\Models\MonitorResult;
use App\Services\MonitoringMetricsService;
use Carbon\CarbonInterval;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Filament\Resources\Monitors\Widgets\MonitorResponseTimeChart;
use App\Filament\Resources\Monitors\Widgets\MonitorUptimeTimeline;

class ViewMonitor extends ViewRecord
{
    protected static string $resource = MonitorResource::class;

    protected function getHeaderWidgets(): array
{
    return [
        MonitorResponseTimeChart::class,
        MonitorUptimeTimeline::class,
    ];
}

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Monitor')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Monitor'),

                        TextEntry::make('project.name')
                            ->label('Project'),

                        TextEntry::make('url')
                            ->label('URL'),

                        TextEntry::make('type')
                            ->label('Type'),

                        TextEntry::make('status')
                            ->label('Monitor Status')
                            ->badge(),

                        TextEntry::make('interval')
                            ->label('Check Interval')
                            ->suffix(' seconds'),

                        TextEntry::make('timeout')
                            ->label('Timeout')
                            ->suffix(' seconds'),

                        TextEntry::make('last_checked_at')
                            ->label('Last Checked')
                            ->dateTime(),

                        TextEntry::make('last_success_at')
                            ->label('Last Success')
                            ->dateTime(),

                        TextEntry::make('last_failure_at')
                            ->label('Last Failure')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Performance')
                    ->schema([
                        TextEntry::make('uptime_24h')
                            ->label('24h Uptime')
                            ->state(function ($record) {
                                $metrics = app(
                                    MonitoringMetricsService::class
                                );

                                return number_format(
                                    $metrics->uptime($record, 24),
                                    2
                                ) . '%';
                            })
                            ->badge()
                            ->color('success'),

                        TextEntry::make('uptime_7d')
                            ->label('7d Uptime')
                            ->state(function ($record) {
                                $metrics = app(
                                    MonitoringMetricsService::class
                                );

                                return number_format(
                                    $metrics->uptime($record, 168),
                                    2
                                ) . '%';
                            })
                            ->badge()
                            ->color('success'),

                        TextEntry::make('uptime_30d')
                            ->label('30d Uptime')
                            ->state(function ($record) {
                                $metrics = app(
                                    MonitoringMetricsService::class
                                );

                                return number_format(
                                    $metrics->uptime($record, 720),
                                    2
                                ) . '%';
                            })
                            ->badge()
                            ->color('success'),

                        TextEntry::make('average_response')
                            ->label('Average Response')
                            ->state(function ($record) {
                                $metrics = app(
                                    MonitoringMetricsService::class
                                );

                                $value = $metrics->averageResponseTime(
                                    $record,
                                    24
                                );

                                return $value !== null
                                    ? round($value) . ' ms'
                                    : '—';
                            }),

                        TextEntry::make('p95_response')
                            ->label('P95 Response')
                            ->state(function ($record) {
                                $metrics = app(
                                    MonitoringMetricsService::class
                                );

                                $value = $metrics->p95ResponseTime(
                                    $record,
                                    24
                                );

                                return $value !== null
                                    ? round($value) . ' ms'
                                    : '—';
                            }),

                        TextEntry::make('downtime_24h')
                            ->label('24h Downtime')
                            ->state(function ($record) {
                                $metrics = app(
                                    MonitoringMetricsService::class
                                );

                                $seconds = $metrics->downtime(
                                    $record,
                                    24
                                );

                                return CarbonInterval::seconds($seconds)
                                    ->cascade()
                                    ->forHumans();
                            }),

                        TextEntry::make('incidents_24h')
                            ->label('24h Incidents')
                            ->state(function ($record) {
                                $metrics = app(
                                    MonitoringMetricsService::class
                                );

                                return $metrics->incidentCount(
                                    $record,
                                    24
                                );
                            }),
                    ])
                    ->columns(4),

                Section::make('Latest Check')
                    ->schema([
                        TextEntry::make('latest_result')
                            ->label('Result')
                            ->state(function ($record) {
                                $result = MonitorResult::query()
                                    ->where(
                                        'monitor_id',
                                        $record->id
                                    )
                                    ->latest('checked_at')
                                    ->first();

                                if (! $result) {
                                    return 'No checks yet';
                                }

                                if (! $result->is_up) {
                                    return 'DOWN — '
                                        . $result->error_message;
                                }

                                $response = $result->response_time_ms !== null
                                    ? $result->response_time_ms . ' ms'
                                    : '—';

                                return "UP — HTTP "
                                    . $result->status_code
                                    . " — "
                                    . $response;
                            })
                            ->badge()
                            ->color(function ($record) {
                                $result = MonitorResult::query()
                                    ->where(
                                        'monitor_id',
                                        $record->id
                                    )
                                    ->latest('checked_at')
                                    ->first();

                                return $result?->is_up
                                    ? 'success'
                                    : 'danger';
                            }),

                        TextEntry::make('latest_checked_at')
                            ->label('Checked At')
                            ->state(function ($record) {
                                return MonitorResult::query()
                                    ->where(
                                        'monitor_id',
                                        $record->id
                                    )
                                    ->latest('checked_at')
                                    ->value('checked_at');
                            })
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}