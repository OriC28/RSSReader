<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Principal')
                    ->components([
                        TextEntry::make('title')
                            ->label('Título')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->components([
                                TextEntry::make('feed.name')
                                    ->label('Fuente')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('category')
                                    ->label('Categoría')
                                    ->badge(),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge(),
                            ]),
                        TextEntry::make('url')
                            ->label('URL Original')
                            ->url(fn ($record) => $record->url)
                            ->openUrlInNewTab()
                            ->color('info')
                            ->columnSpanFull(),
                    ]),

                Section::make('Contenido y Resumen')
                    ->components([
                        TextEntry::make('summary')
                            ->label('Resumen IA')
                            ->placeholder('El resumen aún no se ha generado.')
                            ->columnSpanFull()
                            ->color('success'),
                        TextEntry::make('content')
                            ->label('Contenido Original')
                            ->placeholder('No hay contenido disponible.')
                            ->columnSpanFull()
                            ->html(),
                    ]),

                Section::make('Metadatos')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextEntry::make('published_at')
                                    ->label('Fecha de publicación')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('newsletter.id')
                                    ->label('Enviado en Newsletter')
                                    ->placeholder('No enviado aún'),
                            ]),
                    ])->collapsible(),
            ]);
    }
}
