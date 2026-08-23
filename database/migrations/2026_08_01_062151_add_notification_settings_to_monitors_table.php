<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitors', function (Blueprint $table) {
            $table->boolean('notify_on_incident')
                ->default(true)
                ->after('status');

            $table->boolean('notify_on_recovery')
                ->default(true)
                ->after('notify_on_incident');

            $table->boolean('email_notifications')
                ->default(false)
                ->after('notify_on_recovery');

            $table->boolean('webhook_notifications')
                ->default(false)
                ->after('email_notifications');

            $table->text('notification_email')
                ->nullable()
                ->after('webhook_notifications');

            $table->text('notification_webhook_url')
                ->nullable()
                ->after('notification_email');
        });
    }

    public function down(): void
    {
        Schema::table('monitors', function (Blueprint $table) {
            $table->dropColumn([
                'notify_on_incident',
                'notify_on_recovery',
                'email_notifications',
                'webhook_notifications',
                'notification_email',
                'notification_webhook_url',
            ]);
        });
    }
};