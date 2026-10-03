<?php

namespace App\Filament\Resources\Newsletters\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewslettersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('articles_count')
                    ->label('Artículos Incluidos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Fecha de envío')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
