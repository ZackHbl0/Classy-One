<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make Ann_id nullable so payments can be created without a school year.
     */
    public function up(): void
    {
        Schema::table('paiement', function (Blueprint $table) {
            $table->unsignedBigInteger('Ann_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiement', function (Blueprint $table) {
            $table->unsignedBigInteger('Ann_id')->nullable(false)->change();
        });
    }
};
