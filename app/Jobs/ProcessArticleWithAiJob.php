<?php

namespace App\Jobs;

use App\Enums\CategoryArticle;
use App\Enums\StatusArticle;
use App\Models\Article;
use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Str;

class ProcessArticleWithAiJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 60;

    private $min_content_length = 100;

    /**
     * Create a new job instance.
     */
    public function __construct(public Article $article)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(GeminiService $gemini): void
    {
        if (Str::wordCount($this->article->content) < $this->min_content_length) {
            $this->article->update(['status' => StatusArticle::SKIPPED]);

            return;
        }

        $response = $gemini->summarizeAndCategorize($this->article->content);

        ['summary' => $summary, 'category' => $category] = $response;

        if (blank($summary)) {
            throw new \Exception('La IA devolvió un resumen vacío o inválido.');
        }

        if (is_null(CategoryArticle::tryFrom($category))) {
            $category = CategoryArticle::OTHER;
        }

        $this->article->update([
            'summary' => $summary,
            'category' => $category,
            'status' => StatusArticle::PROCESSED,
        ]);
    }

    public function middleware()
    {
        return [new RateLimited('api-gemini')];
    }

    public function backoff(): array
    {
        return [10, 30, 90];
    }

    public function failed()
    {
        $this->article->update([
            'status' => StatusArticle::FAILED,
        ]);
    }
}
