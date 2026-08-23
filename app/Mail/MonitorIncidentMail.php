<?php

namespace App\Mail;

use App\Models\Incident;
use App\Models\Monitor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonitorIncidentMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Monitor $monitor,
        public Incident $incident,
        public string $event,
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'incident_opened' =>
                "Monitor Down: {$this->monitor->name}",

            'incident_resolved' =>
                "Monitor Recovered: {$this->monitor->name}",

            default =>
                "Monitor Notification: {$this->monitor->name}",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monitor-incident',
        );
    }
}