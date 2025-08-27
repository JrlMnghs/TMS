<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Index for keyword search on translations (with key length for TEXT column)
        DB::statement('ALTER TABLE translations ADD INDEX idx_translations_value (value(255))');

        // Index for key_name search
        Schema::table('translation_keys', function (Blueprint $table) {
            $table->index('key_name', 'idx_translation_keys_key_name');
        });


        // Composite index for translation_key_id + locale_id
        Schema::table('translations', function (Blueprint $table) {
            $table->index(['translation_key_id', 'locale_id'], 'idx_translations_key_locale');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE translations DROP INDEX idx_translations_value');

        Schema::table('translations', function (Blueprint $table) {
            $table->dropIndex('idx_translations_key_locale');
        });

        Schema::table('translation_keys', function (Blueprint $table) {
            $table->dropIndex('idx_translation_keys_key_name');
        });
    }
};