<?php

namespace App\Jobs;

use App\Enums\StatusArticle;
use App\Enums\StatusFeed;
use App\Models\Article;
use App\Models\Feed;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchRssFeedsJob implements ShouldQueue
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
        $feeds = Feed::where('status', StatusFeed::ACTIVE)->get();

        foreach ($feeds as $feed) {

            // Skip feed if already synchronized less than 30 minutes ago
            if ($feed->last_fetched_at && $feed->last_fetched_at->diffInMinutes(now()) < 30) {
                continue;
            }

            try {
                $response = Http::timeout(15)->get($feed->url);

                if ($response->failed()) {
                    throw new \Exception('Error HTTP: '.$response->status());
                }

                libxml_use_internal_errors(true);

                $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);

                if ($xml === false) {
                    $errors = libxml_get_errors();
                    $errorMessage = 'XML Inválido. Error: '.($errors[0]->message ?? 'Desconocido');
                    libxml_clear_errors();

                    throw new \Exception($errorMessage);
                }

                foreach ($xml->channel->item as $item) {
                    $article = Article::firstOrCreate(
                        ['url' => $item->link],
                        [
                            'feed_id' => $feed->id,
                            'title' => $item->title,
                            'content' => $item->description,
                            'published_at' => Carbon::parse($item->pubDate),
                            'status' => StatusArticle::PENDING,
                        ]
                    );
                    if ($article->wasRecentlyCreated) {
                        // ProcessArticleWithAiJob::dispatch($article);
                    }
                }
                $feed->update([
                    'last_fetched_at' => now(),
                    'last_error_at' => null,
                    'last_error_message' => null,
                ]);
            } catch (\Exception $e) {
                $feed->update([
                    'last_error_at' => now(),
                    'last_error_message' => $e->getMessage(),
                ]);
            }
        }
    }
}
