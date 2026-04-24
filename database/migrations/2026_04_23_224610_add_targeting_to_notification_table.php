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
        Schema::table('notification', function (Blueprint $table) {
            $table->string('target_type')->default('all')->after('categorie');
            $table->json('target_ids')->nullable()->after('target_type');
            $table->string('target_summary')->nullable()->after('target_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification', function (Blueprint $table) {
            $table->dropColumn(['target_type', 'target_ids', 'target_summary']);
        });
    }
};
