<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monitor Notification</title>
</head>

<body>
    <h2>
        @if ($event === 'incident_opened')
            Monitor Down
        @else
            Monitor Recovered
        @endif
    </h2>

    <p>
        <strong>{{ $monitor->name }}</strong>
        has a monitoring status update.
    </p>

    <table cellpadding="8">
        <tr>
            <td><strong>Monitor</strong></td>
            <td>{{ $monitor->name }}</td>
        </tr>

        <tr>
            <td><strong>URL</strong></td>
            <td>{{ $monitor->url }}</td>
        </tr>

        <tr>
            <td><strong>Event</strong></td>
            <td>{{ $event }}</td>
        </tr>

        <tr>
            <td><strong>Status</strong></td>
            <td>{{ $incident->status }}</td>
        </tr>

        <tr>
            <td><strong>Started</strong></td>
            <td>{{ $incident->started_at }}</td>
        </tr>

        @if ($incident->resolved_at)
            <tr>
                <td><strong>Resolved</strong></td>
                <td>{{ $incident->resolved_at }}</td>
            </tr>
        @endif

        @if ($incident->duration_seconds)
            <tr>
                <td><strong>Duration</strong></td>
                <td>{{ $incident->duration_seconds }} seconds</td>
            </tr>
        @endif

        @if ($incident->error_message)
            <tr>
                <td><strong>Error</strong></td>
                <td>{{ $incident->error_message }}</td>
            </tr>
        @endif
    </table>
</body>
</html>