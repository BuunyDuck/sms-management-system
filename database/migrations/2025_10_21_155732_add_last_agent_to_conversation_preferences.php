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
        Schema::table('conversation_preferences', function (Blueprint $table) {
            $table->unsignedBigInteger('last_agent_id')->nullable()->after('send_to_support');
            $table->string('last_agent_name')->nullable()->after('last_agent_id');
            
            $table->foreign('last_agent_id')->references('id')->on('users')->onDelete('set null');
            $table->index('last_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_preferences', function (Blueprint $table) {
            $table->dropForeign(['last_agent_id']);
            $table->dropIndex(['last_agent_id']);
            $table->dropColumn(['last_agent_id', 'last_agent_name']);
        });
    }
};
