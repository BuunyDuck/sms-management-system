<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix BODY column to support emojis (utf8mb4)
     */
    public function up(): void
    {
        // Change BODY column to utf8mb4 to support emojis and 4-byte characters
        DB::statement('ALTER TABLE cat_sms MODIFY BODY LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        
        // Optionally, update other text columns that might contain emojis
        DB::statement('ALTER TABLE cat_sms MODIFY fromname VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        DB::statement('ALTER TABLE cat_sms MODIFY toname VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to utf8 (though this may cause data loss if emojis exist)
        DB::statement('ALTER TABLE cat_sms MODIFY BODY LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci');
        DB::statement('ALTER TABLE cat_sms MODIFY fromname VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci');
        DB::statement('ALTER TABLE cat_sms MODIFY toname VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    }
};

