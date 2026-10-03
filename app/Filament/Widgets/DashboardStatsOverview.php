<?php

namespace App\Filament\Widgets;

use App\Enums\StatusArticle;
use App\Enums\StatusFeed;
use App\Models\Article;
use App\Models\Feed;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsOverview extends StatsOverviewWidget
{

    protected ?string $pollingInterval = '15s';
    protected function getStats(): array
    {
        $nextRun = now()->setTime(8, 0);
        if (! now()->isFriday() || now()->format('H:i') >= '08:00') {
            $nextRun = now()->next('Friday')->setTime(8, 0);
        }

        $failedJobsCount = DB::table('failed_jobs')->count();

        return [
            Stat::make('Feeds Activos', Feed::where('status', StatusFeed::ACTIVE)->count())
                ->description('Fuentes RSS actualmente monitoreadas')
                ->descriptionIcon('heroicon-m-rss')
                ->color('primary'),

            Stat::make('Artículos Procesados (Semana)', Article::where('status', StatusArticle::PROCESSED)
                ->where('updated_at', '>=', now()->startOfWeek())
                ->count())
                ->description('Artículos listos analizados por IA')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Artículos Pendientes', Article::where('status', StatusArticle::PENDING)->count())
                ->description('Aguardando procesamiento en cola')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Trabajos Fallidos', $failedJobsCount)
                ->description($failedJobsCount > 0 ? '¡Requiere atención!' : 'Todo funcionando correctamente')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($failedJobsCount > 0 ? 'danger' : 'success'),

            Stat::make('Próximo Boletín', $nextRun->format('d/m/Y h:i A'))
                ->description('Envío programado automáticamente')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }
}
