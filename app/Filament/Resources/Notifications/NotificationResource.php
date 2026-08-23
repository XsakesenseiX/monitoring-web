<?php

namespace App\Filament\Resources\Notifications;

use App\Filament\Resources\Notifications\Pages\CreateNotification;
use App\Filament\Resources\Notifications\Pages\EditNotification;
use App\Filament\Resources\Notifications\Pages\ListNotifications;
use App\Filament\Resources\Notifications\Pages\ViewNotification;
use App\Models\Notification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedBellAlert;

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

    protected static ?string $pluralModelLabel = 'Notifications';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('monitor.name')
                    ->label('Monitor')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'incident_opened' => 'Incident Opened',
                            'incident_resolved' => 'Incident Resolved',
                            default => str($state)->headline()->toString(),
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'incident_opened' => 'danger',
                            'incident_resolved' => 'success',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('channel')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => str($state)
                            ->headline()
                            ->toString()
                    )
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'sent' => 'success',
                            'failed' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('destination')
                    ->label('Destination')
                    ->placeholder('—')
                    ->limit(40)
                    ->tooltip(
                        fn ($state) => $state
                    ),

                \Filament\Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('channel')
                    ->options([
                        'log' => 'Log',
                        'email' => 'Email',
                        'webhook' => 'Webhook',
                    ]),

                \Filament\Tables\Filters\SelectFilter::make('event')
                    ->options([
                        'incident_opened' => 'Incident Opened',
                        'incident_resolved' => 'Incident Resolved',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotifications::route('/'),
            'view' => ViewNotification::route('/{record}'),
        ];
    }
}