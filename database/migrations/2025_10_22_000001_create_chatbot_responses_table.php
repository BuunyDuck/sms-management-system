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
        Schema::create('chatbot_responses', function (Blueprint $table) {
            $table->id();
            $table->integer('menu_number')->unique(); // 1-20
            $table->string('title', 100); // "SkyConnect Instructions", "IMAP Settings", etc.
            $table->text('message'); // The actual response text (can include <media> tags)
            $table->string('template_file')->nullable(); // Legacy reference to .txt file
            $table->string('image_path')->nullable(); // Path to uploaded image
            $table->boolean('active')->default(true); // Can disable without deleting
            $table->integer('display_order')->default(0); // For custom ordering in menu
            $table->timestamps();
            
            $table->index('menu_number');
            $table->index('active');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_responses');
    }
};

