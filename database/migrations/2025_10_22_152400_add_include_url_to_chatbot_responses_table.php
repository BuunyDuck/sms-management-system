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
            $table->string('include_url', 500)->nullable()->after('footer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_responses', function (Blueprint $table) {
            $table->dropColumn('include_url');
        });
    }
};
