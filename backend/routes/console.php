<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule health reminders to run daily
Schedule::command('health:reminders')
    ->daily()
    ->description('Send health reminders for pets with upcoming health record reviews');

