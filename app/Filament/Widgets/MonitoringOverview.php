<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use App\Models\Monitor;
use App\Models\MonitorResult;
use App\Services\MonitoringMetricsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonitoringOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $metrics = app(MonitoringMetricsService::class);

        $totalMonitors = Monitor::query()->count();

        $upMonitors = Monitor::query()
            ->whereNotNull('last_success_at')
            ->where(function ($query) {
                $query
                    ->whereNull('last_failure_at')
                    ->orWhereColumn(
                        'last_success_at',
                        '>',
                        'last_failure_at'
                    );
            })
            ->count();

        $downMonitors = $totalMonitors - $upMonitors;

        $openIncidents = Incident::query()
            ->where('status', 'open')
            ->count();

        $averageResponse = MonitorResult::query()
            ->where('is_up', true)
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');

        $monitors = Monitor::query()->get();

        $uptime24h = $monitors->avg(
            fn (Monitor $monitor): float =>
                $metrics->uptime($monitor, 24)
        );

        $uptime7d = $monitors->avg(
            fn (Monitor $monitor): float =>
                $metrics->uptime($monitor, 168)
        );

        $uptime30d = $monitors->avg(
            fn (Monitor $monitor): float =>
                $metrics->uptime($monitor, 720)
        );

        return [
            Stat::make(
                'Monitors',
                $totalMonitors
            )
                ->description('Total monitors')
                ->icon('heroicon-o-signal'),

            Stat::make(
                'Up',
                $upMonitors
            )
                ->description('Healthy monitors')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make(
                'Down',
                $downMonitors
            )
                ->description('Unhealthy monitors')
                ->color(
                    $downMonitors > 0
                        ? 'danger'
                        : 'success'
                )
                ->icon('heroicon-o-x-circle'),

            Stat::make(
                'Open Incidents',
                $openIncidents
            )
                ->description('Currently active')
                ->color(
                    $openIncidents > 0
                        ? 'danger'
                        : 'success'
                )
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make(
                '24h Uptime',
                $uptime24h !== null
                    ? number_format($uptime24h, 2) . '%'
                    : '—'
            )
                ->description('Last 24 hours')
                ->color(
                    $uptime24h !== null && $uptime24h >= 99.9
                        ? 'success'
                        : 'warning'
                )
                ->icon('heroicon-o-chart-bar'),

            Stat::make(
                '7d Uptime',
                $uptime7d !== null
                    ? number_format($uptime7d, 2) . '%'
                    : '—'
            )
                ->description('Last 7 days')
                ->color(
                    $uptime7d !== null && $uptime7d >= 99.9
                        ? 'success'
                        : 'warning'
                )
                ->icon('heroicon-o-chart-bar'),

            Stat::make(
                '30d Uptime',
                $uptime30d !== null
                    ? number_format($uptime30d, 2) . '%'
                    : '—'
            )
                ->description('Last 30 days')
                ->color(
                    $uptime30d !== null && $uptime30d >= 99.9
                        ? 'success'
                        : 'warning'
                )
                ->icon('heroicon-o-chart-bar'),

            Stat::make(
                'Avg Response',
                $averageResponse !== null
                    ? round($averageResponse) . ' ms'
                    : '—'
            )
                ->description('Successful checks')
                ->icon('heroicon-o-bolt'),
        ];
    }
}