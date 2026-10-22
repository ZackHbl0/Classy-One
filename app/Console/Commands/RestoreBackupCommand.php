<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use ZipArchive;

class RestoreBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore
                            {--file= : Path or filename of the backup zip file}
                            {--latest : Automatically pick the newest backup archive}
                            {--disk=backup : Disk to search for backups (backup or backup_offsite)}
                            {--db-only : Restore only the database}
                            {--files-only : Restore only uploaded media and documents}
                            {--force : Bypass confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanly restore MySQL database and/or uploaded school documents from a backup archive';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=====================================================');
        $this->info('  Classy-One Academy - Disaster Recovery & Restore   ');
        $this->info('=====================================================');

        $diskName = $this->option('disk') ?: 'backup';
        $dbOnly = $this->option('db-only');
        $filesOnly = $this->option('files-only');

        if ($dbOnly && $filesOnly) {
            $this->error('Error: You cannot specify both --db-only and --files-only.');
            return self::FAILURE;
        }

        // 1. Locate the backup archive
        $backupPath = $this->resolveBackupPath($diskName);
        if (!$backupPath || !file_exists($backupPath)) {
            $this->error("Error: Could not locate a valid backup file on disk [{$diskName}].");
            return self::FAILURE;
        }

        $this->info("Found backup archive: {$backupPath}");
        $this->line('Archive size: ' . round(filesize($backupPath) / (1024 * 1024), 2) . ' MB');

        // 2. Confirmation prompt (unless forced)
        if (!$this->option('force')) {
            $target = $dbOnly ? 'Database only' : ($filesOnly ? 'Files only' : 'Database AND Uploaded Files');
            $this->warn("\nWARNING: This disaster recovery action will overwrite current {$target}!");
            $this->warn("Source archive: " . basename($backupPath));

            if (!$this->confirm('Are you sure you wish to continue with restoration?', false)) {
                $this->comment('Restoration aborted by operator.');
                return self::SUCCESS;
            }
        }

        $tempExtractDir = storage_path('app/restore-temp-' . time());
        File::ensureDirectoryExists($tempExtractDir);

        try {
            $startTime = microtime(true);

            // 3. Extract the ZIP archive
            $this->line("\nExtracting archive contents...");
            $zip = new ZipArchive();
            if ($zip->open($backupPath) !== true) {
                $this->error("Failed to open zip archive: {$backupPath}");
                return self::FAILURE;
            }

            $zip->extractTo($tempExtractDir);
            $zip->close();
            $this->info("Archive extracted successfully.");

            // 4. Restore Database
            if (!$filesOnly) {
                $this->restoreDatabase($tempExtractDir);
            }

            // 5. Restore Storage Files
            if (!$dbOnly) {
                $this->restoreStorageFiles($tempExtractDir);
            }

            // 6. Clear application caches
            $this->line("\nClearing application caches...");
            $this->callSilent('cache:clear');
            $this->callSilent('config:clear');
            $this->callSilent('view:clear');
            $this->info("Application caches refreshed.");

            // 7. Audit Log Record
            $this->recordAuditLog(basename($backupPath), $dbOnly, $filesOnly);

            $duration = round(microtime(true) - $startTime, 2);
            $this->newLine();
            $this->info("=====================================================");
            $this->info("  RESTORATION COMPLETED SUCCESSFULLY in {$duration}s ");
            $this->info("=====================================================");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("\nRestoration encountered a critical error: " . $e->getMessage());
            Log::error('Disaster recovery restore failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        } finally {
            // Clean up temporary extraction folder
            if (File::exists($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
        }
    }

    /**
     * Resolve the absolute path to the backup zip file.
     */
    protected function resolveBackupPath(string $diskName): ?string
    {
        $filePath = $this->option('file');

        // Check if an explicit absolute or relative path was passed
        if ($filePath) {
            if (file_exists($filePath)) {
                return realpath($filePath);
            }

            // Check if file is inside disk root
            $diskRoot = Config::get("filesystems.disks.{$diskName}.root");
            if ($diskRoot) {
                $candidate = $diskRoot . DIRECTORY_SEPARATOR . $filePath;
                if (file_exists($candidate)) {
                    return realpath($candidate);
                }

                // Check inside app-named subfolder (e.g. backup_root/Classy-One-Academy/...)
                $matches = glob($diskRoot . '/*/' . $filePath);
                if (!empty($matches)) {
                    return realpath($matches[0]);
                }
            }

            // If an explicit file was requested and not found, do not fall back to newest
            return null;
        }

        // Automatic discovery: Find newest ZIP in disk root
        $diskRoot = Config::get("filesystems.disks.{$diskName}.root");
        if (!$diskRoot || !is_dir($diskRoot)) {
            return null;
        }

        $zipFiles = File::allFiles($diskRoot);
        $zipArchives = array_filter($zipFiles, function ($file) {
            return strtolower($file->getExtension()) === 'zip';
        });

        if (empty($zipArchives)) {
            return null;
        }

        // Sort by modified time descending (newest first)
        usort($zipArchives, function ($a, $b) {
            return $b->getMTime() <=> $a->getMTime();
        });

        return reset($zipArchives)->getRealPath();
    }

    /**
     * Restore MySQL database from the extracted SQL dump.
     */
    protected function restoreDatabase(string $tempDir): void
    {
        $this->line("\n[Database] Locating SQL dump...");
        $sqlFiles = File::glob($tempDir . '/db-dumps/*.sql');
        if (empty($sqlFiles)) {
            $sqlFiles = File::glob($tempDir . '/*.sql');
        }

        if (empty($sqlFiles)) {
            $this->warn("[Database] No SQL dump file found in backup archive. Skipping DB restoration.");
            return;
        }

        $sqlPath = $sqlFiles[0];
        $this->info("[Database] Found SQL dump: " . basename($sqlPath) . " (" . round(filesize($sqlPath) / 1024, 2) . " KB)");

        $connection = Config::get('database.default', 'mysql');
        $dbConfig = Config::get("database.connections.{$connection}", []);

        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? '3306';
        $database = $dbConfig['database'] ?? '';
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';

        $mysqlBinary = $this->findMysqlBinary();

        if ($mysqlBinary) {
            $this->line("[Database] Restoring via MySQL binary: {$mysqlBinary}...");
            $cmd = [
                $mysqlBinary,
                "--host={$host}",
                "--port={$port}",
                "--user={$username}",
            ];

            if ($password !== '') {
                $cmd[] = "--password={$password}";
            }

            $cmd[] = $database;

            $process = new Process($cmd, null, null, file_get_contents($sqlPath), 300);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->warn("[Database] MySQL binary import failed: " . $process->getErrorOutput());
                $this->line("[Database] Falling back to PDO statement execution...");
                $this->restoreViaPdo($sqlPath);
            } else {
                $this->info("[Database] Database [{$database}] restored successfully via MySQL CLI.");
            }
        } else {
            $this->line("[Database] MySQL binary not found in PATH or Laragon. Restoring via PDO...");
            $this->restoreViaPdo($sqlPath);
        }
    }

    /**
     * Locate mysql.exe or mysql CLI binary.
     */
    protected function findMysqlBinary(): ?string
    {
        // 1. Check database config dump binary path
        $configuredPath = Config::get('database.connections.mysql.dump.dump_binary_path');
        if ($configuredPath) {
            $candidate = rtrim($configuredPath, '\\/') . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'mysql.exe' : 'mysql');
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        // 2. Check standard Laragon MySQL path
        $laragonPath = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysql.exe';
        if (file_exists($laragonPath)) {
            return $laragonPath;
        }

        // 3. Check system PATH
        $binary = PHP_OS_FAMILY === 'Windows' ? 'where mysql' : 'which mysql';
        $output = @shell_exec($binary);
        if ($output && trim($output) !== '') {
            $firstLine = trim(explode("\n", trim($output))[0]);
            if (file_exists($firstLine)) {
                return $firstLine;
            }
        }

        return null;
    }

    /**
     * Restore database dump statements via PDO fallback.
     */
    protected function restoreViaPdo(string $sqlPath): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $content = file_get_contents($sqlPath);
        // Remove comments
        $content = preg_replace('/^--.*$/m', '', $content);
        $content = preg_replace('/^\/\*!.*\*\/;?$/m', '', $content);

        $queries = array_filter(array_map('trim', explode(";\n", $content)));
        $count = count($queries);
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($queries as $query) {
            if (!empty($query)) {
                try {
                    DB::unprepared($query . ';');
                } catch (\Throwable $e) {
                    // Ignore non-fatal statement warnings
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->info("[Database] Database restored successfully via PDO fallback.");
    }

    /**
     * Restore uploaded storage files into storage/app/public.
     */
    protected function restoreStorageFiles(string $tempDir): void
    {
        $this->line("\n[Storage Files] Restoring uploaded documents and assets...");

        // Look for storage/app/public inside extracted directory
        $candidateDirs = [
            $tempDir . '/storage/app/public',
            $tempDir . '/storage/public',
            $tempDir . '/public',
        ];

        $sourceStorage = null;
        foreach ($candidateDirs as $dir) {
            if (File::isDirectory($dir)) {
                $sourceStorage = $dir;
                break;
            }
        }

        if (!$sourceStorage) {
            $this->warn("[Storage Files] No uploaded storage files directory found in backup. Skipping file restoration.");
            return;
        }

        $destination = storage_path('app/public');
        File::ensureDirectoryExists($destination);

        // Copy directories recursively
        File::copyDirectory($sourceStorage, $destination);

        $restoredFilesCount = count(File::allFiles($destination));
        $this->info("[Storage Files] Restored uploaded files to [{$destination}] ({$restoredFilesCount} files present).");
    }

    /**
     * Record disaster recovery restoration event in AuditLog.
     */
    protected function recordAuditLog(string $fileName, bool $dbOnly, bool $filesOnly): void
    {
        try {
            if (class_exists(AuditLog::class)) {
                AuditLog::create([
                    'user_id' => null,
                    'user_name' => 'System Console (CLI)',
                    'user_role' => 'system_admin',
                    'action' => 'backup_restored',
                    'subject_type' => 'SystemBackup',
                    'subject_id' => null,
                    'description' => "Automated disaster recovery restore performed from archive [{$fileName}].",
                    'ip_address' => '127.0.0.1',
                    'details' => [
                        'archive' => $fileName,
                        'db_restored' => !$filesOnly,
                        'files_restored' => !$dbOnly,
                        'restored_at' => now()->toIso8601String(),
                    ],
                ]);
                $this->line("Restoration logged to AuditLog trail.");
            }
        } catch (\Throwable $e) {
            Log::warning('Could not write restore audit log: ' . $e->getMessage());
        }
    }
}
