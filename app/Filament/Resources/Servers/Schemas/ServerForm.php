<?php

namespace App\Filament\Resources\Servers\Schemas;

use App\Models\Project;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->label('Project')
                    ->options(Project::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('host')
                    ->label('Host / IP')
                    ->required()
                    ->maxLength(255),

                TextInput::make('port')
                    ->numeric()
                    ->default(22)
                    ->required(),

                Select::make('environment')
                    ->options([
                        'production' => 'Production',
                        'staging' => 'Staging',
                        'development' => 'Development',
                    ])
                    ->default('production')
                    ->required(),

                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}