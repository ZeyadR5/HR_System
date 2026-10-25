<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run')->daily()->at('00:00');

if (env('DEMO_MODE', false)) {
    Schedule::command('demo:reset --force')->dailyAt('00:00');
}

