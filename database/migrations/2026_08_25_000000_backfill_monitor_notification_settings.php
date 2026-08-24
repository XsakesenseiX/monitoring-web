<?php

use App\Models\Monitor;
use App\Observers\MonitorObserver;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed monitor_notification_settings for monitors created
     * before this table existed, from their legacy toggle columns.
     */
    public function up(): void
    {
        Monitor::query()
            ->doesntHave('notificationSettings')
            ->each(fn (Monitor $monitor) => MonitorObserver::seedNotificationSettings($monitor));
    }

    public function down(): void
    {
        //
    }
};
