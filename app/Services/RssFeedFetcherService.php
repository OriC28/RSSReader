<?php

namespace App\Services;

use App\Enums\StatusArticle;
use App\Jobs\ProcessArticleWithAiJob;
use App\Models\Article;
use App\Models\Feed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Throwable;

class RssFeedFetcherService
{
    public function process(Feed $feed): void
    {
        // Skip feed if already synchronized less than 30 minutes ago
        if ($feed->last_fetched_at && $feed->last_fetched_at->diffInMinutes(now()) < 30) {
            return;
        }

        try {
            $xml = $this->fetchXml($feed->url);

            $this->parseAndSaveItems($feed, $xml);

            $feed->update([
                'last_fetched_at' => now(),
                'last_error_at' => null,
                'last_error_message' => null,
            ]);
        } catch (Throwable $e) {
            $feed->update([
                'last_error_at' => now(),
                'last_error_message' => $e->getMessage(),
            ]);
        }
    }

    protected function fetchXml(string $url): SimpleXMLElement
    {
        $response = Http::timeout(15)->get($url);

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

        return $xml;
    }

    protected function parseAndSaveItems(Feed $feed, SimpleXMLElement $xml): void
    {
        foreach ($xml->channel->item as $item) {
            $article = Article::firstOrCreate(
                ['url' => (string) $item->link],
                [
                    'feed_id' => $feed->id,
                    'title' => (string) $item->title,
                    'content' => (string) $item->description,
                    'published_at' => Carbon::parse((string) $item->pubDate),
                    'status' => StatusArticle::PENDING,
                ]
            );

            if ($article->wasRecentlyCreated) {
                ProcessArticleWithAiJob::dispatch($article);
            }
        }
    }
}
