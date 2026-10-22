<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('document_requests', 'reference')) {
                $table->string('reference', 64)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('document_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('admin_note');
            }
            if (!Schema::hasColumn('document_requests', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('document_requests', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
            if (!Schema::hasColumn('document_requests', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('processed_at');
            }
            if (!Schema::hasColumn('document_requests', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });

        // Ensure status column allows French workflow values (nouveau, en_cours, pret, rejete)
        DB::statement("ALTER TABLE `document_requests` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'nouveau'");

        // Populate unique verification reference for existing records
        $records = DB::table('document_requests')->whereNull('reference')->get(['id']);
        foreach ($records as $r) {
            DB::table('document_requests')->where('id', $r->id)->update([
                'reference' => 'DOC-2026-' . str_pad($r->id, 5, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (Schema::hasColumn('document_requests', 'reference')) {
                $table->dropColumn('reference');
            }
            if (Schema::hasColumn('document_requests', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('document_requests', 'processed_by')) {
                $table->dropColumn('processed_by');
            }
            if (Schema::hasColumn('document_requests', 'processed_at')) {
                $table->dropColumn('processed_at');
            }
            if (Schema::hasColumn('document_requests', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('document_requests', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
