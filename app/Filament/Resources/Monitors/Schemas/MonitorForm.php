<?php

namespace App\Filament\Resources\Monitors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MonitorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Monitor')
                    ->schema([
                        Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->options([
                                'http' => 'HTTP',
                            ])
                            ->default('http')
                            ->required(),

                        TextInput::make('url')
                            ->url()
                            ->required()
                            ->maxLength(2048),

                        TextInput::make('interval')
                            ->label('Check Interval')
                            ->numeric()
                            ->suffix('seconds')
                            ->default(60)
                            ->minValue(10)
                            ->required(),

                        TextInput::make('timeout')
                            ->label('Timeout')
                            ->numeric()
                            ->suffix('seconds')
                            ->default(10)
                            ->minValue(1)
                            ->required(),

                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'paused' => 'Paused',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Notifications')
                    ->description('Sets the default notification settings when this monitor is first created. To fine-tune settings per channel and event afterwards, use the "Notification Settings" tab below.')
                    ->schema([
                        Toggle::make('notify_on_incident')
                            ->label('Notify on Incident')
                            ->helperText('Send a notification when the monitor goes down.')
                            ->default(true),

                        Toggle::make('notify_on_recovery')
                            ->label('Notify on Recovery')
                            ->helperText('Send a notification when the monitor recovers.')
                            ->default(true),

                        Toggle::make('email_notifications')
                            ->label('Email Notifications')
                            ->helperText('Enable email notifications for this monitor.')
                            ->default(false),

                        TextInput::make('notification_email')
                            ->label('Notification Email')
                            ->email()
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('email_notifications'))
                            ->required(fn ($get) => $get('email_notifications')),

                        Toggle::make('webhook_notifications')
                            ->label('Webhook Notifications')
                            ->helperText('Send notification events to an HTTP webhook.')
                            ->default(false),

                        TextInput::make('notification_webhook_url')
                            ->label('Webhook URL')
                            ->url()
                            ->maxLength(2048)
                            ->visible(fn ($get) => $get('webhook_notifications'))
                            ->required(fn ($get) => $get('webhook_notifications')),
                    ])
                    ->columns(2),
            ]);
    }
}