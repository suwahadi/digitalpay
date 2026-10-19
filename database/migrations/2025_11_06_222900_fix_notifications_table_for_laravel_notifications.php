<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Check if table exists and has old structure
        if (Schema::hasTable('notifications')) {
            // Check if it's the old structure (has 'title' column)
            if (Schema::hasColumn('notifications', 'title')) {
                // Backup old data if needed (optional - uncomment if you want to preserve data)
                // DB::statement('CREATE TABLE notifications_backup AS SELECT * FROM notifications');
                
                // Drop the old notifications table
                Schema::drop('notifications');
            } else {
                // Table exists with new structure, check if it has all required columns
                if (Schema::hasColumn('notifications', 'notifiable_type') && 
                    Schema::hasColumn('notifications', 'notifiable_id') &&
                    Schema::hasColumn('notifications', 'data')) {
                    // Table already has the correct structure, skip creation
                    return;
                }
                // Table exists but doesn't have correct structure, drop it
                Schema::drop('notifications');
            }
        }
        
        // Create new notifications table compatible with Laravel's notification system
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // Store notification class name (e.g., App\Notifications\OrderCreatedNotification)
            $table->morphs('notifiable'); // Creates notifiable_type and notifiable_id (includes index)
            $table->text('data'); // JSON data
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        
        // Recreate old structure if needed
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'success', 'warning', 'error'])->default('info');
            $table->string('action_url', 500)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['is_read']);
            $table->index(['created_at']);
        });
    }
};
