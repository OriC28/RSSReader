<?php

namespace App\Jobs;

use App\Enums\StatusArticle;
use App\Enums\StatusNewsletter;
use App\Mail\SendNewsletter;
use App\Models\Article;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateNewsletterJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $articles = Article::where('status', StatusArticle::PROCESSED)
            ->whereNull('newsletter_id')
            ->get();

        if ($articles->count() < 3) {
            Log::error('No hay suficientes artículos procesados y sin newsletter.');
            throw new \Exception('No hay suficientes artículos para generar la newsletter (mínimo 3).');
        }

        $newsletter = Newsletter::create([
            'subject' => 'Newsletter semanal',
            'articles_count' => $articles->count(),
        ]);

        $articles->each(function (Article $article) use ($newsletter) {
            $article->newsletter_id = $newsletter->id;
            $article->save();
        });

        $user = User::first();

        Mail::to($user->email)->queue(new SendNewsletter($articles));

        $newsletter->update([
            'status' => StatusNewsletter::SENT,
            'sent_at' => now(),
        ]);
    }
}
