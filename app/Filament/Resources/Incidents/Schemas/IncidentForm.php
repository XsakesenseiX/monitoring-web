<?php

namespace App\Filament\Resources\Incidents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class IncidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('monitor_id')
                    ->label('Monitor')
                    ->relationship('monitor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'resolved' => 'Resolved',
                    ])
                    ->required(),

                DateTimePicker::make('started_at')
                    ->required(),

                DateTimePicker::make('resolved_at'),

                TextInput::make('duration_seconds')
                    ->label('Duration')
                    ->numeric()
                    ->suffix(' seconds'),

                TextInput::make('failure_count')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Textarea::make('error_message')
                    ->columnSpanFull(),
            ]);
    }
}