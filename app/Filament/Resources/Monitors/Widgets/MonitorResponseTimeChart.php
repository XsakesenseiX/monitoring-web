<?php

namespace App\Filament\Resources\Monitors\Widgets;

use App\Models\Monitor;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class MonitorResponseTimeChart extends ChartWidget
{
    protected ?string $heading = 'Response Time — Last 24 Hours';

    protected ?string $maxHeight = '300px';

    public ?Model $record = null;

    protected function getData(): array
    {
        /** @var Monitor $monitor */
        $monitor = $this->record;

        $results = $monitor->results()
            ->where('checked_at', '>=', now()->subHours(24))
            ->whereNotNull('response_time_ms')
            ->orderBy('checked_at')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Response Time (ms)',
                    'data' => $results
                        ->map(
                            fn ($result) => $result->response_time_ms
                        )
                        ->values()
                        ->toArray(),
                    'fill' => false,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $results
                ->map(
                    fn ($result) =>
                        $result->checked_at->format('H:i')
                )
                ->values()
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}