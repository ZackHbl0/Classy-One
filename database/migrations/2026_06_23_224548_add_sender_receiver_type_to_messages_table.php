<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add sender_type / receiver_type so we can tell apart whether an ID
     * belongs to the `users` table (professors/admins) or the `student` table.
     *
     * Existing rows all come from user↔user chat, so they default to 'user'.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('sender_type', 10)->default('user')->after('sender_id');
            $table->string('receiver_type', 10)->default('user')->after('receiver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['sender_type', 'receiver_type']);
        });
    }
};
