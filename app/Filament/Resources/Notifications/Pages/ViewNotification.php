<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('monitor.name')
                    ->label('Monitor'),

                TextEntry::make('monitor.url')
                    ->label('URL'),

                TextEntry::make('event')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'incident_opened' => 'Incident Opened',
                            'incident_resolved' => 'Incident Resolved',
                            default => str($state)->headline()->toString(),
                        }
                    ),

                TextEntry::make('channel')
                    ->label('Channel')
                    ->badge(),

                TextEntry::make('status')
                    ->label('Status')
                    ->badge(),

                TextEntry::make('destination')
                    ->label('Destination')
                    ->placeholder('—'),

                TextEntry::make('sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->placeholder('Not sent'),

                TextEntry::make('error_message')
                    ->label('Error')
                    ->placeholder('No error'),

                TextEntry::make('incident.status')
                    ->label('Incident Status')
                    ->badge(),

                TextEntry::make('incident.started_at')
                    ->label('Incident Started')
                    ->dateTime(),

                TextEntry::make('incident.resolved_at')
                    ->label('Incident Resolved')
                    ->dateTime()
                    ->placeholder('Not resolved'),

                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label('Updated At')
                    ->dateTime(),
            ]);
    }
}