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
        Schema::table('cat_sms', function (Blueprint $table) {
            // Add bot interaction flag
            $table->boolean('is_bot_interaction')->default(false)->after('TOCOUNTRY');
            
            // Add index for efficient filtering
            $table->index('is_bot_interaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cat_sms', function (Blueprint $table) {
            $table->dropIndex(['is_bot_interaction']);
            $table->dropColumn('is_bot_interaction');
        });
    }
};

