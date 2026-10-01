<?php

use App\Enums\CategoryArticle;
use App\Enums\StatusArticle;
use App\Jobs\ProcessArticleWithAiJob;
use App\Models\Article;
use App\Models\Feed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
});

it('skips article when content is shorter than 100 words', function () {
    $feed = Feed::create(['name' => 'Test', 'url' => 'https://test.com/xml', 'status' => 'active']);

    $article = Article::create([
        'feed_id' => $feed->id,
        'title' => 'Short Article',
        'url' => 'https://test.com/1',
        'content' => 'This is a very short article with only a few words.',
        'status' => StatusArticle::PENDING->value,
    ]);

    ProcessArticleWithAiJob::dispatchSync($article);

    $article->refresh();

    expect($article->status->value)->toBe(StatusArticle::SKIPPED->value)
        ->and($article->summary)->toBeNull()
        ->and($article->category)->toBeNull();
});

it('processes article and updates summary and category', function () {
    $feed = Feed::create(['name' => 'Test', 'url' => 'https://test.com/xml', 'status' => 'active']);

    $article = Article::create([
        'feed_id' => $feed->id,
        'title' => 'Long Article',
        'url' => 'https://test.com/2',
        'content' => str_repeat('Word ', 150),
        'status' => StatusArticle::PENDING->value,
    ]);

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/*';

    Http::fake([
        $url => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => json_encode(['summary' => 'Resumen generado por IA.', 'category' => 'Tecnología'])],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    ProcessArticleWithAiJob::dispatchSync($article);

    $article->refresh();

    expect($article->status->value)->toBe(StatusArticle::PROCESSED->value)
        ->and($article->summary)->toBe('Resumen generado por IA.')
        ->and($article->category->value)->toBe('Tecnología');
});

it('falls back to Otros category when AI returns an invalid category', function () {
    $feed = Feed::create(['name' => 'Test', 'url' => 'https://test.com/xml', 'status' => 'active']);

    $article = Article::create([
        'feed_id' => $feed->id,
        'title' => 'Long Article',
        'url' => 'https://test.com/3',
        'content' => str_repeat('Word ', 150),
        'status' => StatusArticle::PENDING->value,
    ]);

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/*';

    Http::fake([
        $url => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => json_encode(['summary' => 'Resumen exitoso.', 'category' => 'CategoriaInventada'])],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    ProcessArticleWithAiJob::dispatchSync($article);

    $article->refresh();

    expect($article->category->value)->toBe(CategoryArticle::OTHER->value);
});

it('throws exception when AI returns empty summary', function () {
    $feed = Feed::create(['name' => 'Test', 'url' => 'https://test.com/xml', 'status' => 'active']);

    $article = Article::create([
        'feed_id' => $feed->id,
        'title' => 'Long Article',
        'url' => 'https://test.com/4',
        'content' => str_repeat('Word ', 150),
        'status' => StatusArticle::PENDING->value,
    ]);

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/*';

    Http::fake([
        $url => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => json_encode(['summary' => '', 'category' => 'Tecnología'])],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    expect(fn () => ProcessArticleWithAiJob::dispatchSync($article))
        ->toThrow(Exception::class, 'La IA devolvió un resumen vacío o inválido.');
});
