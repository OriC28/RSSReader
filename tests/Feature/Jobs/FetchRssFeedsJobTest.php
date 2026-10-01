<?php

use App\Enums\StatusArticle;
use App\Enums\StatusFeed;
use App\Jobs\FetchRssFeedsJob;
use App\Jobs\ProcessArticleWithAiJob;
use App\Models\Article;
use App\Models\Feed;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
});

it('fetches rss feeds and saves new articles', function () {
    Queue::fake([ProcessArticleWithAiJob::class]);

    $feed = Feed::create([
        'name' => 'Blog',
        'url' => 'https://test.com/xml',
        'status' => StatusFeed::ACTIVE->value,
    ]);

    $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><item><title>Title 1</title><link>https://test.com/1</link><description>Content 1</description><pubDate>Wed, 30 Sep 2026 12:00:00 GMT</pubDate></item></channel></rss>';

    Http::fake([
        'https://test.com/xml' => Http::response($xml, 200),
    ]);

    FetchRssFeedsJob::dispatchSync();

    expect(Article::count())->toBe(1);

    $article = Article::first();
    expect($article->title)->toBe('Title 1')
        ->and($article->url)->toBe('https://test.com/1')
        ->and($article->status->value)->toBe(StatusArticle::PENDING->value);

    Queue::assertPushed(ProcessArticleWithAiJob::class, fn ($job) => $job->article->id === $article->id);
});

it('skips feed if last fetched was less than 30 minutes ago', function () {
    Queue::fake([ProcessArticleWithAiJob::class]);

    Feed::create([
        'name' => 'Blog',
        'url' => 'https://test.com/xml',
        'status' => StatusFeed::ACTIVE->value,
        'last_fetched_at' => now()->subMinutes(15),
    ]);

    FetchRssFeedsJob::dispatchSync();

    expect(Article::count())->toBe(0);
    Http::assertNothingSent();
});

it('does not process inactive feeds', function () {
    Queue::fake([ProcessArticleWithAiJob::class]);

    Feed::create([
        'name' => 'Blog',
        'url' => 'https://test.com/xml',
        'status' => StatusFeed::INACTIVE->value,
    ]);

    FetchRssFeedsJob::dispatchSync();

    expect(Article::count())->toBe(0);
    Http::assertNothingSent();
});

it('updates feed with error when HTTP request fails', function () {
    $feed = Feed::create([
        'name' => 'Blog',
        'url' => 'https://test.com/xml',
        'status' => StatusFeed::ACTIVE->value,
    ]);

    Http::fake([
        'https://test.com/xml' => Http::response('Server Error', 500),
    ]);

    FetchRssFeedsJob::dispatchSync();

    $feed->refresh();

    expect($feed->last_error_at)->not->toBeNull()
        ->and($feed->last_error_message)->toContain('Error HTTP: 500');
});

it('updates feed with error when XML is invalid', function () {
    $feed = Feed::create([
        'name' => 'Blog',
        'url' => 'https://test.com/xml',
        'status' => StatusFeed::ACTIVE->value,
    ]);

    Http::fake([
        'https://test.com/xml' => Http::response('Not an XML', 200),
    ]);

    FetchRssFeedsJob::dispatchSync();

    $feed->refresh();

    expect($feed->last_error_at)->not->toBeNull()
        ->and($feed->last_error_message)->toContain('XML Inválido');
});

it('does not duplicate articles with the same URL', function () {
    Queue::fake([ProcessArticleWithAiJob::class]);

    $feed = Feed::create([
        'name' => 'Blog',
        'url' => 'https://test.com/xml',
        'status' => StatusFeed::ACTIVE->value,
    ]);

    Article::create([
        'feed_id' => $feed->id,
        'title' => 'Old Title',
        'url' => 'https://test.com/1',
        'content' => 'Old Content',
        'status' => StatusArticle::PENDING->value,
    ]);

    $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><item><title>Title 1</title><link>https://test.com/1</link><description>Content 1</description><pubDate>Wed, 30 Sep 2026 12:00:00 GMT</pubDate></item></channel></rss>';

    Http::fake([
        'https://test.com/xml' => Http::response($xml, 200),
    ]);

    FetchRssFeedsJob::dispatchSync();

    expect(Article::count())->toBe(1);
});

it('dispatches AI job only for newly created articles', function () {
    Queue::fake([ProcessArticleWithAiJob::class]);

    $feed = Feed::create([
        'name' => 'Blog',
        'url' => 'https://test.com/xml',
        'status' => StatusFeed::ACTIVE->value,
    ]);

    Article::create([
        'feed_id' => $feed->id,
        'title' => 'Already Exists',
        'url' => 'https://test.com/old',
        'content' => 'Content',
        'status' => StatusArticle::PENDING->value,
    ]);

    $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>
        <item><title>Old</title><link>https://test.com/old</link><description>C</description><pubDate>Wed, 30 Sep 2026 12:00:00 GMT</pubDate></item>
        <item><title>New</title><link>https://test.com/new</link><description>C</description><pubDate>Wed, 30 Sep 2026 12:00:00 GMT</pubDate></item>
    </channel></rss>';

    Http::fake([
        'https://test.com/xml' => Http::response($xml, 200),
    ]);

    FetchRssFeedsJob::dispatchSync();

    Queue::assertPushed(ProcessArticleWithAiJob::class, 1);
});

it('syncs only the received feed if passed to the constructor', function () {
    Queue::fake([ProcessArticleWithAiJob::class]);

    $feed1 = Feed::create([
        'name' => 'Blog 1',
        'url' => 'https://test.com/xml1',
        'status' => StatusFeed::ACTIVE->value,
    ]);

    $feed2 = Feed::create([
        'name' => 'Blog 2',
        'url' => 'https://test.com/xml2',
        'status' => StatusFeed::ACTIVE->value,
    ]);

    $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><item><title>Title</title><link>https://test.com/1</link><description>Content</description><pubDate>Wed, 30 Sep 2026 12:00:00 GMT</pubDate></item></channel></rss>';

    Http::fake([
        'https://test.com/xml1' => Http::response($xml, 200),
        'https://test.com/xml2' => Http::response($xml, 200),
    ]);

    FetchRssFeedsJob::dispatchSync($feed1);

    expect(Article::count())->toBe(1)
        ->and(Article::first()->feed_id)->toBe($feed1->id);

    Http::assertSent(fn ($request) => $request->url() == 'https://test.com/xml1');
    Http::assertNotSent(fn ($request) => $request->url() == 'https://test.com/xml2');
});

it('throws exception if the received feed is inactive', function () {
    $feed = Feed::create([
        'name' => 'Inactive Blog',
        'url' => 'https://test.com/xml',
        'status' => StatusFeed::INACTIVE->value,
    ]);

    expect(fn () => FetchRssFeedsJob::dispatchSync($feed))
        ->toThrow(Exception::class, "El feed 'Inactive Blog' no está activo y no puede ser sincronizado.");
});
