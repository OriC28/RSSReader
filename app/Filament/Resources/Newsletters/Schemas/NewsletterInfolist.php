<?php

namespace App\Filament\Resources\Newsletters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Boletín')
                    ->components([
                        TextEntry::make('subject')
                            ->label('Asunto')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->components([
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge(),

                                TextEntry::make('articles_count')
                                    ->label('Artículos Incluidos')
                                    ->numeric(),

                                TextEntry::make('sent_at')
                                    ->label('Fecha de envío')
                                    ->dateTime()
                                    ->placeholder('No enviado aún'),
                            ]),
                    ]),

                Section::make('Registro del Sistema')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextEntry::make('created_at')
                                    ->label('Creado el')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->label('Última actualización')
                                    ->dateTime(),
                            ]),
                    ])->collapsible()->collapsed(),
            ]);
    }
}
