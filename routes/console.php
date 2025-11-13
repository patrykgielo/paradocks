<?php

use App\Jobs\Email\CleanupOldEmailLogsJob;
use App\Jobs\Email\SendAdminDigestJob;
use App\Jobs\Email\SendFollowUpEmailsJob;
use App\Jobs\Email\SendReminderEmailsJob;
use App\Jobs\Sms\CleanupOldSmsLogsJob;
use App\Jobs\Sms\SendFollowUpSmsJob;
use App\Jobs\Sms\SendReminderSmsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Email System Scheduled Jobs
|--------------------------------------------------------------------------
|
| Automated email jobs for reminders, follow-ups, admin digests, and cleanup
|
*/

// Send appointment reminders (24h and 2h before)
// Runs: Every hour
Schedule::job(new SendReminderEmailsJob)
    ->hourly()
    ->withoutOverlapping()
    ->name('email:send-reminders')
    ->onOneServer();

// Send follow-up emails to completed appointments
// Runs: Every hour
Schedule::job(new SendFollowUpEmailsJob)
    ->hourly()
    ->withoutOverlapping()
    ->name('email:send-followups')
    ->onOneServer();

// Send daily admin digest with statistics
// Runs: Daily at 8:00 AM
Schedule::job(new SendAdminDigestJob)
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->name('email:admin-digest')
    ->onOneServer();

// Cleanup old email logs (GDPR 90-day retention)
// Runs: Daily at 2:00 AM
Schedule::job(new CleanupOldEmailLogsJob)
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->name('email:cleanup-logs')
    ->onOneServer();

/*
|--------------------------------------------------------------------------
| SMS System Scheduled Jobs
|--------------------------------------------------------------------------
|
| Automated SMS jobs for reminders, follow-ups, and cleanup
|
*/

// Send SMS appointment reminders (24h and 2h before)
// Runs: Every hour
Schedule::job(new SendReminderSmsJob)
    ->hourly()
    ->withoutOverlapping()
    ->name('sms:send-reminders')
    ->onOneServer();

// Send follow-up SMS to completed appointments
// Runs: Every hour
Schedule::job(new SendFollowUpSmsJob)
    ->hourly()
    ->withoutOverlapping()
    ->name('sms:send-followups')
    ->onOneServer();

// Cleanup old SMS logs (GDPR 90-day retention)
// Runs: Daily at 2:30 AM (30 minutes after email cleanup)
Schedule::job(new CleanupOldSmsLogsJob)
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->name('sms:cleanup-logs')
    ->onOneServer();
