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
        // 1. Enhance student table with discounts & scholarship attributes
        Schema::table('student', function (Blueprint $table) {
            if (!Schema::hasColumn('student', 'remise_montant')) {
                $table->decimal('remise_montant', 10, 2)->default(0.00)->after('frais_scolarite');
            }
            if (!Schema::hasColumn('student', 'remise_type')) {
                $table->string('remise_type', 50)->default('aucune')->after('remise_montant');
            }
            if (!Schema::hasColumn('student', 'remise_motif')) {
                $table->string('remise_motif', 255)->nullable()->after('remise_type');
            }
        });

        // 2. Enhance paiement table with reference, notes, staff tracker, and timestamps
        Schema::table('paiement', function (Blueprint $table) {
            if (!Schema::hasColumn('paiement', 'reference')) {
                $table->string('reference', 64)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('paiement', 'remise_appliquee')) {
                $table->decimal('remise_appliquee', 10, 2)->default(0.00)->after('montant');
            }
            if (!Schema::hasColumn('paiement', 'notes')) {
                $table->text('notes')->nullable()->after('statut');
            }
            if (!Schema::hasColumn('paiement', 'recorded_by')) {
                $table->unsignedBigInteger('recorded_by')->nullable()->after('notes');
                $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('paiement', 'created_at')) {
                $table->timestamps();
            }
        });

        // 3. Backfill any existing payments with unique references and timestamps
        $payments = DB::table('paiement')->whereNull('reference')->get();
        foreach ($payments as $p) {
            $year = date('Y', strtotime($p->dateEcheance ?? 'now'));
            $ref = sprintf('REF-%s-%05d', $year, $p->id);
            DB::table('paiement')->where('id', $p->id)->update([
                'reference' => $ref,
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
        Schema::table('paiement', function (Blueprint $table) {
            if (Schema::hasColumn('paiement', 'recorded_by')) {
                $table->dropForeign(['recorded_by']);
                $table->dropColumn('recorded_by');
            }
            $cols = ['reference', 'remise_appliquee', 'notes', 'created_at', 'updated_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('paiement', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('student', function (Blueprint $table) {
            $cols = ['remise_montant', 'remise_type', 'remise_motif'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('student', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
