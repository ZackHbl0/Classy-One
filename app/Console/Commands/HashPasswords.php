<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HashPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:hash-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hashes any plain-text passwords in the student table to bcrypt.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting password migration...');
        
        $students = Student::all();
        $migratedCount = 0;
        $skippedCount = 0;

        foreach ($students as $student) {
            // A bcrypt hash via Laravel is typically 60 characters and starts with $2y$.
            // If the password does NOT look like a bcrypt hash, we hash it.
            if (!Str::startsWith($student->password, '$2y$') || strlen($student->password) !== 60) {
                $student->password = Hash::make($student->password);
                $student->save();
                $migratedCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->info("Migration complete!");
        $this->info("Passwords hashed: {$migratedCount}");
        $this->info("Already hashed (skipped): {$skippedCount}");
    }
}
