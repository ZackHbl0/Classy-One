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
        Schema::table('student', function (Blueprint $column) {
            $column->boolean('event_notifications')->default(true)->after('fcmToken');
            $column->boolean('payment_notifications')->default(true)->after('event_notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $column) {
            $column->dropColumn(['event_notifications', 'payment_notifications']);
        });
    }
};
