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
        Schema::table('document_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('document_requests', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('idStudent')
                    ->constrained('parents')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (Schema::hasColumn('document_requests', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
