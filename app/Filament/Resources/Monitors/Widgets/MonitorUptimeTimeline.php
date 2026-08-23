<?php

namespace App\Filament\Resources\Monitors\Widgets;

use App\Models\Monitor;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class MonitorUptimeTimeline extends ChartWidget
{
    protected ?string $heading = 'Uptime — Last 24 Hours';

    protected ?string $maxHeight = '180px';

    public ?Model $record = null;

    protected function getData(): array
    {
        /** @var Monitor $monitor */
        $monitor = $this->record;

        $end = now();
        $start = $end->copy()->subHours(24);

        $results = $monitor->results()
            ->whereBetween('checked_at', [$start, $end])
            ->orderBy('checked_at')
            ->get();

        $labels = [];
        $data = [];

        foreach ($results as $result) {
            $labels[] = $result->checked_at->format('H:i');

            $data[] = $result->is_up ? 1 : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Status',
                    'data' => $data,
                    'fill' => true,
                    'stepped' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 1,
                    'ticks' => [
                        'stepSize' => 1,
                        'callback' => 'function(value) {
                            return value === 1 ? "UP" : "DOWN";
                        }',
                    ],
                ],
            ],
        ];
    }
}