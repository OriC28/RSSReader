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

    public $timeout = 60;

    private $min_content_length = 100;

    public function retryUntil(): \Carbon\CarbonInterface
    {
        return now()->addDays(3);
    }

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

        try {
            $response = $gemini->summarizeAndCategorize($this->article->content);
        } catch (\Exception $e) {
            // If the Google quota was exceeded despite the Rate Limiter
            if (Str::contains($e->getMessage(), ['429', 'RESOURCE_EXHAUSTED', 'quota'])) {
                // Return the job to the queue so it can be retried the next day
                $this->release(now()->addDay());
                return;
            }

            throw $e;
        }

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
