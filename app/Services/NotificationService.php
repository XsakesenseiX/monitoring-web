<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\MonitorNotificationSetting;
use App\Models\Notification;

class NotificationService
{
    public function incidentOpened(
        Monitor $monitor,
        Incident $incident
    ): array {
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
        return $monitor->notificationSettings()
            ->where('event', $event)
            ->where('enabled', true)
            ->get()
            ->map(fn (MonitorNotificationSetting $setting) => $this->createNotification(
                $monitor,
                $incident,
                $setting,
                $event
            ))
            ->all();
    }

    private function createNotification(
        Monitor $monitor,
        Incident $incident,
        MonitorNotificationSetting $setting,
        string $event
    ): Notification {
        $notification = Notification::create([
            'monitor_id' => $monitor->id,
            'incident_id' => $incident->id,
            'channel' => $setting->channel,
            'event' => $event,
            'status' => 'pending',
            'destination' => $setting->destination,
        ]);

        SendNotificationJob::dispatch($notification->id);

        return $notification;
    }
}