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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable(); // admin, secretaire, professeur
            $table->string('action'); // create, update, delete, status_change, login, export
            $table->string('subject_type')->nullable(); // Note / Grade, Absence, Document, Inscription, Paiement, Notification, Planning
            $table->string('subject_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['user_role', 'created_at']);
            $table->index('action');
            $table->index('subject_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
