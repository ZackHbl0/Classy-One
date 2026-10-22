<?php

use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

return [

    'backup' => [
        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => env('APP_NAME', 'Classy-One-Academy'),

        'source' => [
            'files' => [
                /*
                 * The list of directories and files that will be included in the backup.
                 * Includes all school uploaded documents (courses, certificates, receipts, media).
                 */
                'include' => [
                    storage_path('app/public'),
                    database_path(),
                ],

                /*
                 * Directories and files that will be excluded from the backup.
                 */
                'exclude' => [
                    storage_path('framework'),
                    storage_path('logs'),
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                'follow_links' => false,
                'ignore_unreadable_directories' => true,
                'relative_path' => base_path(),
            ],

            /*
             * The names of the connections to the databases that should be backed up.
             */
            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        /*
         * The database dump can be compressed to decrease disk space usage.
         */
        'database_dump_compressor' => null,

        'database_dump_file_timestamp_format' => null,
        'database_dump_filename_base' => 'database',
        'database_dump_file_extension' => '',

        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9,
            'filename_prefix' => 'classy-one-backup-',

            /*
             * Destination disks:
             * 'backup': Local storage inside storage/app/backups
             * 'backup_offsite': External / remote storage (S3 / remote directory / cloud)
             */
            'disks' => array_filter([
                'backup',
                env('BACKUP_OFFSITE_ENABLED', true) ? 'backup_offsite' : null,
            ]),

            'continue_on_failure' => true,
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => 'default',
        'verify_backup' => true,
        'tries' => 2,
        'retry_delay' => 10,
    ],

    /*
     * Backup notification settings.
     */
    'notifications' => [
        'notifications' => [
            BackupHasFailedNotification::class => ['mail'],
            UnhealthyBackupWasFoundNotification::class => ['mail'],
            CleanupHasFailedNotification::class => ['mail'],
            BackupWasSuccessfulNotification::class => ['mail'],
            HealthyBackupWasFoundNotification::class => ['mail'],
            CleanupWasSuccessfulNotification::class => ['mail'],
        ],

        'notifiable' => Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS', 'admin@classy-one.test')),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'system@classy-one.test'),
                'name' => env('MAIL_FROM_NAME', 'Classy-One System Backup'),
            ],
        ],

        'slack' => [
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL', ''),
            'channel' => null,
            'username' => 'Classy-One Backup Bot',
            'icon' => null,
        ],

        'discord' => [
            'webhook_url' => env('BACKUP_DISCORD_WEBHOOK_URL', ''),
            'username' => 'Classy-One Backup Bot',
            'avatar_url' => '',
        ],

        'webhook' => [
            'url' => env('BACKUP_GENERIC_WEBHOOK_URL', ''),
        ],
    ],

    /*
     * Backup Health Monitoring rules.
     */
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'Classy-One-Academy'),
            'disks' => ['backup', 'backup_offsite'],
            'health_checks' => [
                MaximumAgeInDays::class => 1,
                MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    /*
     * Cleanup strategy and retention rules.
     */
    'cleanup' => [
        'strategy' => DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 1,
            'delete_oldest_backups_when_using_more_megabytes_than' => 10000,
        ],

        'tries' => 1,
        'retry_delay' => 0,
    ],

];
