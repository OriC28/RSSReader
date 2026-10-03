<?php

namespace App\Filament\Resources\Newsletters\Pages;

use App\Enums\StatusArticle;
use App\Filament\Resources\Newsletters\NewsletterResource;
use App\Jobs\GenerateNewsletterJob;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

class ListNewsletters extends ListRecords
{
    protected static string $resource = NewsletterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Vista Previa')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading('Vista Previa del Boletín')
                ->modalContent(function () {
                    $pendingArticles = Article::where('status', StatusArticle::PROCESSED)
                        ->whereNull('newsletter_id')
                        ->get();

                    $html = View::make('emails.newsletter_email_template', [
                        'articles' => $pendingArticles,
                    ])->render();

                    return new HtmlString(
                        '<iframe srcdoc="'.htmlentities($html, ENT_QUOTES, 'UTF-8').'" width="100%" height="600px" style="border: none; border-radius: 8px; background: white;"></iframe>'
                    );
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar'),

            Action::make('send_now')
                ->label('Enviar ahora')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('¿Enviar boletín ahora?')
                ->modalDescription('Se enviará un boletín con los artículos actualmente procesados que no han sido enviados.')
                ->action(function () {
                    try {
                        GenerateNewsletterJob::dispatchSync();

                        Notification::make()
                            ->title('Boletín enviado con éxito')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al generar el boletín')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
