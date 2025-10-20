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
        Schema::create('chatbot_sessions_log', function (Blueprint $table) {
            $table->id();
            
            // Session identification
            $table->uuid('session_id');
            $table->string('phone', 15);
            
            // Navigation tracking
            $table->string('menu_path', 255)->nullable();
            $table->string('user_input', 255)->nullable();
            $table->text('bot_response')->nullable();
            $table->string('response_template', 50)->nullable();
            
            // Timing
            $table->dateTime('session_start');
            $table->dateTime('session_end')->nullable();
            $table->dateTime('interaction_time');
            $table->integer('time_in_menu')->default(0); // seconds
            
            // Session outcome
            $table->enum('exit_type', ['explicit', 'timeout', 'error', 'active'])->default('active');
            $table->boolean('completed_successfully')->default(false);
            
            // Metadata
            $table->string('from_number', 15); // Which MTSKY number (752-4335 or 215-2048)
            $table->boolean('has_media')->default(false);
            $table->integer('message_id')->nullable(); // Link to cat_sms.id
            
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for performance
            $table->index('session_id');
            $table->index('phone');
            $table->index('session_start');
            $table->index('from_number');
            $table->index('response_template');
            $table->index(['session_start', 'exit_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions_log');
    }
};

