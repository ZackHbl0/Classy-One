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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'two_factor_secret')) {
                    $table->text('two_factor_secret')->nullable()->after('password');
                }
                if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                    $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
                }
                if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                    $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
                }
                if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                    $table->boolean('two_factor_enabled')->default(false)->after('two_factor_confirmed_at');
                }
            });
        }

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('audit_logs', 'signature')) {
                    $table->string('signature', 64)->nullable()->after('details');
                }
                if (!Schema::hasColumn('audit_logs', 'previous_signature')) {
                    $table->string('previous_signature', 64)->nullable()->after('signature');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = ['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'two_factor_enabled'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $columns = ['signature', 'previous_signature'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('audit_logs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
