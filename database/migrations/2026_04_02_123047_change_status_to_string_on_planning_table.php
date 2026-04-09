<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE planning MODIFY status VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy rollback for ENUM without knowing previous values, leaving empty.
    }
};
