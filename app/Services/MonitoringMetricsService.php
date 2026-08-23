<?php

namespace App\Services;

use App\Models\Monitor;
use Carbon\Carbon;

class MonitoringMetricsService
{
    public function uptime(
        Monitor $monitor,
        int $hours
    ): float {
        $end = Carbon::now();
        $start = $end->copy()->subHours($hours);

        $totalSeconds = $start->diffInSeconds($end);

        if ($totalSeconds <= 0) {
            return 100.0;
        }

        $downtimeSeconds = $this->downtimeSeconds(
            $monitor,
            $start,
            $end
        );

        return $this->uptimePercentage(
            $totalSeconds,
            $downtimeSeconds
        );
    }

    public function downtime(
        Monitor $monitor,
        int $hours
    ): int {
        $end = Carbon::now();
        $start = $end->copy()->subHours($hours);

        return $this->downtimeSeconds(
            $monitor,
            $start,
            $end
        );
    }

    public function uptimePercentage(
        int $totalSeconds,
        int $downtimeSeconds
    ): float {
        if ($totalSeconds <= 0) {
            return 100.0;
        }

        $uptime = (
            ($totalSeconds - $downtimeSeconds)
            / $totalSeconds
        ) * 100;

        return round(
            max(0, min(100, $uptime)),
            2
        );
    }

    public function averageResponseTime(
        Monitor $monitor,
        int $hours
    ): ?float {
        $since = Carbon::now()->subHours($hours);

        return $monitor->results()
            ->where('checked_at', '>=', $since)
            ->where('is_up', true)
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');
    }

    public function p95ResponseTime(
        Monitor $monitor,
        int $hours
    ): ?float {
        $since = Carbon::now()->subHours($hours);

        $values = $monitor->results()
            ->where('checked_at', '>=', $since)
            ->where('is_up', true)
            ->whereNotNull('response_time_ms')
            ->orderBy('response_time_ms')
            ->pluck('response_time_ms')
            ->values();

        if ($values->isEmpty()) {
            return null;
        }

        $index = (int) ceil($values->count() * 0.95) - 1;

        $index = max(
            0,
            min($index, $values->count() - 1)
        );

        return (float) $values->get($index);
    }

    public function incidentCount(
        Monitor $monitor,
        int $hours
    ): int {
        $since = Carbon::now()->subHours($hours);

        return $monitor->incidents()
            ->where('started_at', '>=', $since)
            ->count();
    }

    protected function downtimeSeconds(
        Monitor $monitor,
        Carbon $start,
        Carbon $end
    ): int {
        $downtimeSeconds = 0;

        $incidents = $monitor->incidents()
            ->where('started_at', '<', $end)
            ->where(function ($query) use ($start) {
                $query
                    ->whereNull('resolved_at')
                    ->orWhere('resolved_at', '>', $start);
            })
            ->get();

        foreach ($incidents as $incident) {
            $incidentStart = $incident->started_at->greaterThan($start)
                ? $incident->started_at
                : $start;

            $incidentEnd = $incident->resolved_at
                ? (
                    $incident->resolved_at->lessThan($end)
                        ? $incident->resolved_at
                        : $end
                )
                : $end;

            if ($incidentEnd->greaterThan($incidentStart)) {
                $downtimeSeconds += $incidentStart->diffInSeconds(
                    $incidentEnd
                );
            }
        }

        return $downtimeSeconds;
    }
}