<?php

namespace App\Jobs;

use App\Mail\MonitorIncidentMail;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $notificationId
    ) {
    }

    public function handle(): void
    {
        $notification = Notification::with([
            'monitor',
            'incident',
        ])->find($this->notificationId);

        if (! $notification) {
            return;
        }

        if ($notification->status === 'sent') {
            return;
        }

        try {
            match ($notification->channel) {
                'log' => $this->sendLog($notification),
                'email' => $this->sendEmail($notification),
                'webhook' => $this->sendWebhook($notification),
                default => throw new \RuntimeException(
                    "Unsupported notification channel: {$notification->channel}"
                ),
            };

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $notification->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function sendLog(Notification $notification): void
    {
        logger()->info('Monitor notification', [
            'notification_id' => $notification->id,
            'monitor_id' => $notification->monitor_id,
            'incident_id' => $notification->incident_id,
            'event' => $notification->event,
        ]);
    }

    private function sendEmail(Notification $notification): void
    {
        $monitor = $notification->monitor;
        $incident = $notification->incident;

        if (! $monitor->notification_email) {
            throw new \RuntimeException(
                'Notification email is not configured.'
            );
        }

        Mail::to($monitor->notification_email)->send(
            new MonitorIncidentMail(
                $monitor,
                $incident,
                $notification->event
            )
        );
    }

    private function sendWebhook(Notification $notification): void
    {
        $monitor = $notification->monitor;
        $incident = $notification->incident;

        if (! $monitor->notification_webhook_url) {
            throw new \RuntimeException(
                'Notification webhook URL is not configured.'
            );
        }

        $response = Http::timeout(10)
            ->post(
                $monitor->notification_webhook_url,
                [
                    'event' => $notification->event,

                    'monitor' => [
                        'id' => $monitor->id,
                        'name' => $monitor->name,
                        'url' => $monitor->url,
                        'status' => $monitor->status,
                    ],

                    'incident' => [
                        'id' => $incident->id,
                        'status' => $incident->status,
                        'started_at' => $incident->started_at,
                        'resolved_at' => $incident->resolved_at,
                        'duration_seconds' => $incident->duration_seconds,
                        'failure_count' => $incident->failure_count,
                        'error_message' => $incident->error_message,
                    ],
                ]
            );

        $response->throw();
    }
}