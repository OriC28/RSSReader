<?php

use App\Jobs\FetchRssFeedsJob;
use App\Jobs\GenerateNewsletterJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new FetchRssFeedsJob)->everyTwoHours();

Schedule::job(new GenerateNewsletterJob)->fridays()->at('08:00');
