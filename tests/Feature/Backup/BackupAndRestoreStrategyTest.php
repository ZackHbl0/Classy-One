<?php

namespace Tests\Feature\Backup;

use App\Models\AuditLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class BackupAndRestoreStrategyTest extends TestCase
{
    /**
     * Test that backup configuration defines correct sources and destination disks.
     */
    public function test_backup_configuration_is_properly_defined(): void
    {
        $backupConfig = Config::get('backup');

        $this->assertNotNull($backupConfig, 'config/backup.php must be loaded.');

        // Check source files
        $includeFiles = $backupConfig['backup']['source']['files']['include'] ?? [];
        $this->assertContains(storage_path('app/public'), $includeFiles, 'Backup must include storage/app/public.');
        $this->assertContains(database_path(), $includeFiles, 'Backup must include database directory.');

        // Check destination disks
        $destinationDisks = $backupConfig['backup']['destination']['disks'] ?? [];
        $this->assertContains('backup', $destinationDisks, 'Destination disks must contain local "backup" disk.');
        $this->assertContains('backup_offsite', $destinationDisks, 'Destination disks must contain secure "backup_offsite" disk.');

        // Check retention policy
        $this->assertArrayHasKey('cleanup', $backupConfig);
        $this->assertArrayHasKey('default_strategy', $backupConfig['cleanup']);
        $this->assertGreaterThan(0, $backupConfig['cleanup']['default_strategy']['keep_all_backups_for_days']);
    }

    /**
     * Test that filesystems configuration contains backup and backup_offsite disks.
     */
    public function test_filesystems_contains_backup_and_backup_offsite_disks(): void
    {
        $backupDisk = Config::get('filesystems.disks.backup');
        $this->assertNotNull($backupDisk, 'Disk [backup] must be defined in filesystems.php.');
        $this->assertEquals(storage_path('app/backups'), $backupDisk['root']);

        $offsiteDisk = Config::get('filesystems.disks.backup_offsite');
        $this->assertNotNull($offsiteDisk, 'Disk [backup_offsite] must be defined in filesystems.php.');
    }

    /**
     * Test that scheduled backup tasks are registered in Laravel Scheduler.
     */
    public function test_laravel_scheduler_contains_all_backup_commands(): void
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $commands = $events->map(function ($event) {
            return $event->command;
        })->filter()->values()->all();

        $commandsString = implode(' | ', $commands);

        $this->assertStringContainsString('backup:run', $commandsString, 'Scheduler must contain backup:run.');
        $this->assertStringContainsString('backup:clean', $commandsString, 'Scheduler must contain backup:clean.');
        $this->assertStringContainsString('backup:monitor', $commandsString, 'Scheduler must contain backup:monitor.');
        $this->assertStringContainsString('backup:run --only-db', $commandsString, 'Scheduler must contain weekly backup:run --only-db.');
    }

    /**
     * Test backup:restore fails when both --db-only and --files-only are specified.
     */
    public function test_backup_restore_command_validates_mutually_exclusive_flags(): void
    {
        $exitCode = Artisan::call('backup:restore', [
            '--db-only' => true,
            '--files-only' => true,
            '--force' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('cannot specify both --db-only and --files-only', Artisan::output());
    }

    /**
     * Test backup:restore fails gracefully when file is not found.
     */
    public function test_backup_restore_command_fails_gracefully_when_archive_is_not_found(): void
    {
        $exitCode = Artisan::call('backup:restore', [
            '--file' => 'nonexistent_test_backup_archive_12345.zip',
            '--force' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Could not locate a valid backup file', Artisan::output());
    }

    /**
     * Test backup:restore successfully restores files from a mock backup archive and writes an audit log.
     */
    public function test_backup_restore_command_extracts_and_restores_cleanly(): void
    {
        // 1. Create a dummy test zip archive with a sample uploaded file
        $tempDir = storage_path('app/testing-backup');
        File::ensureDirectoryExists($tempDir);
        $zipPath = $tempDir . '/mock-backup-restore-test.zip';

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('storage/app/public/test_restore_beacon.txt', 'RESTORE_TEST_SUCCESS_BEACON');
        $zip->addFromString('db-dumps/mysql-dummy.sql', '-- MySQL dummy dump' . PHP_EOL . 'SELECT 1;' . PHP_EOL);
        $zip->close();

        $this->assertFileExists($zipPath);

        // 2. Run backup:restore command with files-only
        $initialAuditCount = AuditLog::where('action', 'backup_restored')->count();

        $exitCode = Artisan::call('backup:restore', [
            '--file' => $zipPath,
            '--files-only' => true,
            '--force' => true,
        ]);

        $output = Artisan::output();
        $this->assertEquals(0, $exitCode, "Restore command failed with output: {$output}");
        $this->assertStringContainsString('RESTORATION COMPLETED SUCCESSFULLY', $output);

        // 3. Verify the file was restored to storage/app/public
        $restoredBeacon = storage_path('app/public/test_restore_beacon.txt');
        $this->assertFileExists($restoredBeacon);
        $this->assertEquals('RESTORE_TEST_SUCCESS_BEACON', file_get_contents($restoredBeacon));

        // 4. Verify AuditLog entry was recorded
        $newAuditCount = AuditLog::where('action', 'backup_restored')->count();
        $this->assertGreaterThan($initialAuditCount, $newAuditCount);

        $latestLog = AuditLog::where('action', 'backup_restored')->latest('id')->first();
        $this->assertNotNull($latestLog);
        $this->assertStringContainsString('mock-backup-restore-test.zip', $latestLog->description);

        // Clean up test beacon & mock zip
        if (File::exists($restoredBeacon)) {
            File::delete($restoredBeacon);
        }
        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }
        File::deleteDirectory($tempDir);
    }
}
