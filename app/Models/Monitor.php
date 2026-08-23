<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Incident;

class Monitor extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'type',
        'url',
        'interval',
        'timeout',
        'status',
        'last_checked_at',
        'last_success_at',
        'last_failure_at',
        'notify_on_incident',
        'notify_on_recovery',
        'email_notifications',
        'webhook_notifications',
        'notification_email',
        'notification_webhook_url',
    ];

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'notify_on_incident' => 'boolean',
            'notify_on_recovery' => 'boolean',
            'email_notifications' => 'boolean',
            'webhook_notifications' => 'boolean',
        ];
    }

    public function results(): HasMany
    {
        return $this->hasMany(MonitorResult::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function incidents(): HasMany
    {
    return $this->hasMany(Incident::class);
    }

    public function notifications(): HasMany
    {
    return $this->hasMany(Notification::class);
    }

    public function notificationSettings(): HasMany
    {
    return $this->hasMany(MonitorNotificationSetting::class);
    }
}