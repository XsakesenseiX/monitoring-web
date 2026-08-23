<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\Notification;

class NotificationService
{
    public function incidentOpened(
        Monitor $monitor,
        Incident $incident
    ): array {
        if (! $monitor->notify_on_incident) {
            return [];
        }

        return $this->dispatchNotifications(
            $monitor,
            $incident,
            'incident_opened'
        );
    }

    public function incidentResolved(
        Monitor $monitor,
        Incident $incident
    ): array {
        if (! $monitor->notify_on_recovery) {
            return [];
        }

        return $this->dispatchNotifications(
            $monitor,
            $incident,
            'incident_resolved'
        );
    }

    private function dispatchNotifications(
        Monitor $monitor,
        Incident $incident,
        string $event
    ): array {
        $notifications = [];

        $notifications[] = $this->createNotification(
            $monitor,
            $incident,
            'log',
            $event
        );

        if (
            $monitor->email_notifications &&
            filled($monitor->notification_email)
        ) {
            $notifications[] = $this->createNotification(
                $monitor,
                $incident,
                'email',
                $event
            );
        }

        if (
            $monitor->webhook_notifications &&
            filled($monitor->notification_webhook_url)
        ) {
            $notifications[] = $this->createNotification(
                $monitor,
                $incident,
                'webhook',
                $event
            );
        }

        return $notifications;
    }

    private function createNotification(
        Monitor $monitor,
        Incident $incident,
        string $channel,
        string $event
    ): Notification {
        $notification = Notification::create([
            'monitor_id' => $monitor->id,
            'incident_id' => $incident->id,
            'channel' => $channel,
            'event' => $event,
            'status' => 'pending',
        ]);

        SendNotificationJob::dispatch($notification->id);

        return $notification;
    }
}