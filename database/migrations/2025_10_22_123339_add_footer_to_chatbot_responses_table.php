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
        Schema::table('chatbot_responses', function (Blueprint $table) {
            $table->text('footer')->nullable()->after('message')->default('™');
        });
        
        // Set default footer for all existing responses
        DB::table('chatbot_responses')->update(['footer' => '™']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_responses', function (Blueprint $table) {
            $table->dropColumn('footer');
        });
    }
};
