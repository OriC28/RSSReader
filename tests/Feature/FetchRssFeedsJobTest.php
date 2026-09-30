<?php

use App\Jobs\FetchRssFeedsJob;
use App\Models\Article;
use App\Models\Feed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('fetches rss feeds and saves new articles', function () {
    $feed = Feed::create([
        'name' => 'Blog de Prueba',
        'url' => 'https://ejemplo.com/feed.xml',
    ]);

    $fakeXml = '<?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0">
            <channel>
                <item>
                    <title>Noticia de Prueba</title>
                    <link>https://ejemplo.com/noticia-1</link>
                    <description>Este es un artículo de prueba de más de 100 palabras...</description>
                    <pubDate>Wed, 30 Sep 2026 12:00:00 GMT</pubDate>
                </item>
            </channel>
        </rss>';

    Http::fake([
        'https://ejemplo.com/feed.xml' => Http::response($fakeXml, 200),
    ]);

    FetchRssFeedsJob::dispatchSync();

    expect(Article::count())->toBe(1);

    $article = Article::first();
    expect($article->title)->toBe('Noticia de Prueba')
        ->and($article->url)->toBe('https://ejemplo.com/noticia-1')
        ->and($article->feed_id)->toBe($feed->id)
        ->and($article->status)->toBe('pending');
});
