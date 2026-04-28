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
            $table->unsignedBigInteger('idStudent')->nullable()->after('target_summary');

            // Assuming student table primary key is idStudent
            $table->foreign('idStudent')->references('idStudent')->on('student')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification', function (Blueprint $table) {
            $table->dropForeign(['idStudent']);
            $table->dropColumn('idStudent');
        });
    }
};
