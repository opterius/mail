<?php

use App\Jobs\ProcessScheduledEmails;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process scheduled emails every minute
Schedule::job(new ProcessScheduledEmails)->everyMinute();

// Clean up expired snoozes (older than 7 days past snooze_until) weekly
Schedule::command('model:prune', ['--model' => 'App\Models\SnoozedEmail'])->weekly();
