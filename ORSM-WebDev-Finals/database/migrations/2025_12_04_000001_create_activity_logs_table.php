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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('action'); // e.g., 'create', 'update', 'delete', 'login', 'logout'
            $table->string('subject_type')->nullable(); // e.g., 'Product', 'Order', 'User', 'Settings'
            $table->unsignedBigInteger('subject_id')->nullable(); // ID of the affected resource
            $table->json('changes')->nullable(); // Store what was changed (before/after)
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Add indexes for faster queries
            $table->index('user_id');
            $table->index('action');
            $table->index('subject_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
