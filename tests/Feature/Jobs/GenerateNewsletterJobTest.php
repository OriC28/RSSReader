<?php

use App\Enums\CategoryArticle;
use App\Enums\StatusArticle;
use App\Enums\StatusNewsletter;
use App\Jobs\GenerateNewsletterJob;
use App\Mail\SendNewsletter;
use App\Models\Article;
use App\Models\Feed;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->feed = Feed::create([
        'name' => 'Tech Blog',
        'url' => 'https://test.com/rss',
        'status' => 'active',
    ]);
});

it('throws exception if less than 3 eligible articles exist', function () {
    Article::create([
        'feed_id' => $this->feed->id,
        'title' => 'Article 1',
        'url' => 'https://test.com/1',
        'status' => StatusArticle::PROCESSED->value,
    ]);

    Article::create([
        'feed_id' => $this->feed->id,
        'title' => 'Article 2',
        'url' => 'https://test.com/2',
        'status' => StatusArticle::PROCESSED->value,
    ]);

    expect(fn () => GenerateNewsletterJob::dispatchSync())
        ->toThrow(Exception::class, 'No hay suficientes artículos para generar la newsletter (mínimo 3).');

    expect(Newsletter::count())->toBe(0);
});

it('processes only articles with processed status and null newsletter_id', function () {
    Mail::fake();

    $oldNewsletter = Newsletter::create([
        'subject' => 'Old',
        'status' => StatusNewsletter::SENT->value,
    ]);

    // Already sent
    Article::create([
        'feed_id' => $this->feed->id,
        'title' => 'Old Article',
        'url' => 'https://test.com/old',
        'status' => StatusArticle::PROCESSED->value,
        'newsletter_id' => $oldNewsletter->id,
    ]);

    // Pending (No processed)
    Article::create([
        'feed_id' => $this->feed->id,
        'title' => 'Pending Article',
        'url' => 'https://test.com/pending',
        'status' => StatusArticle::PENDING->value,
    ]);

    // 3 eligible candidates
    for ($i = 1; $i <= 3; $i++) {
        Article::create([
            'feed_id' => $this->feed->id,
            'title' => "Eligible $i",
            'url' => "https://test.com/eligible-$i",
            'status' => StatusArticle::PROCESSED->value,
            'category' => CategoryArticle::TECHNOLOGY->value,
        ]);
    }

    GenerateNewsletterJob::dispatchSync();

    $newsletter = Newsletter::latest('id')->first();

    expect($newsletter->articles_count)->toBe(3);

    $oldArticle = Article::where('url', 'https://test.com/old')->first();
    expect($oldArticle->newsletter_id)->toBe($oldNewsletter->id);

    $pendingArticle = Article::where('url', 'https://test.com/pending')->first();
    expect($pendingArticle->newsletter_id)->toBeNull();
});

it('updates newsletter_id on processed articles and Newsletter fields correctly', function () {
    Mail::fake();

    for ($i = 1; $i <= 4; $i++) {
        Article::create([
            'feed_id' => $this->feed->id,
            'title' => "Eligible $i",
            'url' => "https://test.com/eligible-$i",
            'status' => StatusArticle::PROCESSED->value,
            'category' => CategoryArticle::TECHNOLOGY->value,
        ]);
    }

    GenerateNewsletterJob::dispatchSync();

    $newsletter = Newsletter::latest('id')->first();

    expect($newsletter->subject)->toBe('Newsletter semanal')
        ->and($newsletter->status->value)->toBe(StatusNewsletter::SENT->value)
        ->and($newsletter->sent_at)->not->toBeNull()
        ->and($newsletter->articles_count)->toBe(4);

    // All eligible articles must have the ID of this new newsletter.
    $updatedArticles = Article::where('newsletter_id', $newsletter->id)->count();
    expect($updatedArticles)->toBe(4);
});

it('sends the email correctly to the authenticated user', function () {
    Mail::fake();

    for ($i = 1; $i <= 3; $i++) {
        Article::create([
            'feed_id' => $this->feed->id,
            'title' => "Eligible $i",
            'url' => "https://test.com/eligible-$i",
            'status' => StatusArticle::PROCESSED->value,
            'category' => CategoryArticle::TECHNOLOGY->value,
        ]);
    }

    GenerateNewsletterJob::dispatchSync();

    Mail::assertQueued(SendNewsletter::class, function (SendNewsletter $mail) {
        return $mail->hasTo('admin@test.com') &&
            $mail->articles->where('category', CategoryArticle::TECHNOLOGY)->isNotEmpty();
    });
});

it('renders the newsletter template correctly with all necessary data', function () {
    $article = Article::create([
        'feed_id' => $this->feed->id,
        'title' => 'Article to Render',
        'url' => 'https://test.com/render',
        'summary' => 'This is a mocked summary from AI.',
        'status' => StatusArticle::PROCESSED->value,
        'category' => CategoryArticle::TECHNOLOGY->value,
    ]);

    $mailable = new SendNewsletter(collect([$article]));

    $html = $mailable->render();

    expect($html)
        ->toContain('Tu Boletín Semanal')
        ->toContain('Article to Render')
        ->toContain('https://test.com/render')
        ->toContain('This is a mocked summary from AI.')
        ->toContain('Tecnología')
        ->toContain('Tech Blog');
});

it('renders the empty message correctly in the newsletter template', function () {
    $mailable = new SendNewsletter(collect());

    $html = $mailable->render();

    expect($html)
        ->toContain('Tu Boletín Semanal')
        ->toContain('No hay artículos nuevos esta semana.');
});
