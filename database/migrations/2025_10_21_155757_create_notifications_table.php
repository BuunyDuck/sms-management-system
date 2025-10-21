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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Agent who should be notified
            $table->string('type'); // Type of notification (e.g., 'customer_reply')
            $table->string('phone_number'); // Customer phone number
            $table->string('customer_name')->nullable(); // Customer name for display
            $table->text('message_preview')->nullable(); // Preview of the message
            $table->unsignedBigInteger('message_id')->nullable(); // Link to SMS message
            $table->boolean('read')->default(false); // Has agent seen it?
            $table->timestamp('read_at')->nullable(); // When was it read?
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Foreign key to cat_sms removed - column types may not match
            
            $table->index(['user_id', 'read', 'created_at']);
            $table->index('phone_number');
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
