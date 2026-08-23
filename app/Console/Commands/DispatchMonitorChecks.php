<?php

namespace App\Console\Commands;

use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:dispatch-monitor-checks')]
#[Description('Dispatch due monitor checks to the queue')]
class DispatchMonitorChecks extends Command
{
    public function handle(): int
    {
        $monitors = Monitor::query()
            ->where('status', 'active')
            ->get();

        $dispatched = 0;

        foreach ($monitors as $monitor) {
            $isDue = $monitor->last_checked_at === null
                || $monitor->last_checked_at->addSeconds($monitor->interval)->isPast();

            if (! $isDue) {
                continue;
            }

            CheckMonitorJob::dispatch($monitor->id);

            $dispatched++;

            $this->info(
                "Dispatched monitor #{$monitor->id} ({$monitor->name})"
            );
        }

        $this->info("Total dispatched: {$dispatched}");

        return self::SUCCESS;
    }
}