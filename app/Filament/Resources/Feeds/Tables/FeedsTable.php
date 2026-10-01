<?php

namespace App\Filament\Resources\Feeds\Tables;

use App\Enums\StatusFeed;
use App\Jobs\FetchRssFeedsJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class FeedsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre'),
                TextColumn::make('url')
                    ->searchable(),
                ToggleColumn::make('status')
                    ->label('Estado')
                    ->getStateUsing(fn ($record) => $record->status === StatusFeed::ACTIVE)
                    ->updateStateUsing(fn ($record, $state) => $record->update([
                        'status' => $state ? StatusFeed::ACTIVE : StatusFeed::INACTIVE,
                    ])),
                TextColumn::make('last_fetched_at')
                    ->label('Última sincronización')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_error_at')
                    ->label('Último error')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                self::getSynchronizeAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function getSynchronizeAction(): Action
    {
        return Action::make('sync')
            ->label('Sincronizar ahora')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->action(function ($record) {
                try {
                    FetchRssFeedsJob::dispatchSync($record);
                    Notification::make()
                        ->title("La fuente '$record->name' fue sincronizada correctamente.")
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    logger()->error("Error procesando la fuente {$record->name}: ".$e->getMessage());

                    Notification::make()
                        ->title('Error al sincronizar la fuente.')
                        ->body($e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();
                }
            })
            ->requiresConfirmation()
            ->modalHeading('¿Forzar sincronización?')
            ->modalDescription('Esto descargará los artículos más recientes del feed de inmediato.')
            ->modalSubmitActionLabel('Sí, sincronizar');
    }
}
