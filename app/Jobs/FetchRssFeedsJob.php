<?php

namespace App\Jobs;

use App\Enums\StatusFeed;
use App\Models\Feed;
use App\Services\RssFeedFetcherService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchRssFeedsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ?Feed $feed = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(RssFeedFetcherService $fetcher): void
    {
        if ($this->feed) {
            if ($this->feed->status !== StatusFeed::ACTIVE) {
                throw new Exception("El feed '{$this->feed->name}' no está activo y no puede ser sincronizado.");
            }

            $fetcher->process($this->feed);

            return;
        }

        $feeds = Feed::where('status', StatusFeed::ACTIVE)->get();

        foreach ($feeds as $feed) {
            $fetcher->process($feed);
        }
    }
}
