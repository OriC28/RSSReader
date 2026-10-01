<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\CategoryArticle;
use App\Enums\StatusArticle;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('feed.name')
                    ->label('Fuente')
                    ->searchable(),
                TextColumn::make('title')
                    ->label('Título')
                    ->words(5)
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->searchable(),
                TextColumn::make('summary')
                    ->label('Resumen')
                    ->words(5),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('published_at')
                    ->label('Fecha de publicación')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(StatusArticle::class),
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(CategoryArticle::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultPaginationPageOption(5);
    }
}
