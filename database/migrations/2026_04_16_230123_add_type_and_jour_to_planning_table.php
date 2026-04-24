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
        Schema::table('planning', function (Blueprint $table) {
            $table->string('jour')->nullable()->after('date');
            $table->string('type')->default('COURS')->after('professeur_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning', function (Blueprint $table) {
            $table->dropColumn(['jour', 'type']);
        });
    }
};
