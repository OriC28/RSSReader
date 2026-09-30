<?php

namespace App\Filament\Resources\Feeds\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeedForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la fuente')
                    ->required(),
                TextInput::make('url')
                    ->url()
                    ->required()
                    ->unique(),
            ]);
    }
}
