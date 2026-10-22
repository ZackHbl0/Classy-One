<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Automated Backup & Maintenance Task Scheduler
|--------------------------------------------------------------------------
|
| Configures daily automated database and file backups, offsite exports,
| retention cleanup, and health monitoring via Laravel Scheduler.
|
*/

// Daily Full Backup (Database + Uploaded Files) at 01:00 AM
Schedule::command('backup:run')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::critical('Automated Backup: Daily full backup failed.');
    })
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Automated Backup: Daily full backup completed successfully.');
    });

// Daily Cleanup of Old Backups according to retention policy at 02:00 AM
Schedule::command('backup:clean')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// Daily Health Monitoring & Notification at 03:00 AM
Schedule::command('backup:monitor')
    ->dailyAt('03:00')
    ->withoutOverlapping();

// Weekly Lightweight Database Snapshot on Sundays at 04:00 AM
Schedule::command('backup:run --only-db')
    ->weeklyOn(0, '04:00')
    ->withoutOverlapping();

