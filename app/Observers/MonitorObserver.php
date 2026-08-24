<?php

namespace App\Observers;

use App\Models\Monitor;

class MonitorObserver
{
    /**
     * Seed default notification settings for a newly created monitor.
     *
     * This only runs once, on creation: after that, the settings
     * rows are the source of truth and are edited directly via the
     * Notification Settings relation manager, not overwritten here.
     */
    public function created(Monitor $monitor): void
    {
        static::seedNotificationSettings($monitor);
    }

    public static function seedNotificationSettings(Monitor $monitor): void
    {
        foreach (['incident_opened', 'incident_resolved'] as $event) {
            $notifyEnabled = $event === 'incident_opened'
                ? $monitor->notify_on_incident
                : $monitor->notify_on_recovery;

            $monitor->notificationSettings()->firstOrCreate(
                ['channel' => 'log', 'event' => $event],
                ['enabled' => (bool) $notifyEnabled, 'destination' => null]
            );

            $monitor->notificationSettings()->firstOrCreate(
                ['channel' => 'email', 'event' => $event],
                [
                    'enabled' => (bool) $notifyEnabled
                        && $monitor->email_notifications
                        && filled($monitor->notification_email),
                    'destination' => $monitor->notification_email,
                ]
            );

            $monitor->notificationSettings()->firstOrCreate(
                ['channel' => 'webhook', 'event' => $event],
                [
                    'enabled' => (bool) $notifyEnabled
                        && $monitor->webhook_notifications
                        && filled($monitor->notification_webhook_url),
                    'destination' => $monitor->notification_webhook_url,
                ]
            );
        }
    }
}
