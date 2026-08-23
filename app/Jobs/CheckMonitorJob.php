<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Models\Monitor;
use App\Models\MonitorResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Throwable;

class CheckMonitorJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $monitorId
    ) {
    }

    public function handle(): void
    {
        $monitor = Monitor::find($this->monitorId);

        if (! $monitor) {
            return;
        }

        $checkedAt = now();
        $startedAt = microtime(true);

        $isUp = false;
        $statusCode = null;
        $errorMessage = null;

        try {
            $response = Http::timeout($monitor->timeout)
                ->connectTimeout($monitor->timeout)
                ->get($monitor->url);

            $statusCode = $response->status();
            $isUp = $response->successful();

            if (! $isUp) {
                $errorMessage = "HTTP status {$statusCode}";
            }
        } catch (Throwable $exception) {
            $errorMessage = $exception->getMessage();
        }

        $responseTime = (int) round(
            (microtime(true) - $startedAt) * 1000
        );

        MonitorResult::create([
            'monitor_id' => $monitor->id,
            'is_up' => $isUp,
            'status_code' => $statusCode,
            'response_time_ms' => $responseTime,
            'error_message' => $errorMessage,
            'checked_at' => $checkedAt,
        ]);

        $monitor->update([
            'last_checked_at' => $checkedAt,

            'last_success_at' => $isUp
                ? $checkedAt
                : $monitor->last_success_at,

            'last_failure_at' => $isUp
                ? $monitor->last_failure_at
                : $checkedAt,
        ]);

        if ($isUp) {
            $this->resolveIncident(
                $monitor,
                $checkedAt
            );

            return;
        }

        $this->openIncident(
            $monitor,
            $checkedAt,
            $errorMessage
        );
    }

    private function openIncident(
        Monitor $monitor,
        $checkedAt,
        ?string $errorMessage
    ): void {
        $incident = $monitor->incidents()
            ->where('status', 'open')
            ->latest('started_at')
            ->first();

        /*
         * Existing incident:
         * update failure count only.
         *
         * Do NOT send another notification.
         */
        if ($incident) {
            $incident->update([
                'failure_count' => $incident->failure_count + 1,
                'error_message' => $errorMessage,
            ]);

            return;
        }

        /*
         * New incident.
         */
        $incident = Incident::create([
            'monitor_id' => $monitor->id,
            'status' => 'open',
            'started_at' => $checkedAt,
            'resolved_at' => null,
            'duration_seconds' => null,
            'error_message' => $errorMessage,
            'failure_count' => 1,
        ]);

        /*
         * Send notification only for a newly opened incident.
         */
        app(\App\Services\NotificationService::class)
            ->incidentOpened(
                $monitor,
                $incident
            );
    }

    private function resolveIncident(
        Monitor $monitor,
        $resolvedAt
    ): void {
        $incident = $monitor->incidents()
            ->where('status', 'open')
            ->latest('started_at')
            ->first();

        if (! $incident) {
            return;
        }

        $startedAt = $incident->started_at;

        $durationSeconds = $startedAt
            ? (int) round(
                $startedAt->diffInSeconds($resolvedAt)
            )
            : 0;

        $incident->update([
            'status' => 'resolved',
            'resolved_at' => $resolvedAt,
            'duration_seconds' => $durationSeconds,
        ]);

        /*
         * Send recovery notification only once,
         * when the incident transitions to resolved.
         */
        app(\App\Services\NotificationService::class)
            ->incidentResolved(
                $monitor,
                $incident->fresh()
            );
    }
}