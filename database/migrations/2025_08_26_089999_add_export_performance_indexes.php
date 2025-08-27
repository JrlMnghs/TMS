<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Composite index for export queries (translation_key_id + locale_id)
        // This is the most critical index for export performance
        DB::statement('ALTER TABLE translations ADD INDEX idx_export_performance (translation_key_id, locale_id)');

        // Index for tag filtering in export queries
        DB::statement('ALTER TABLE translation_key_tags ADD INDEX idx_tag_filtering (translation_key_id, tag_id)');

        // Index for locale code lookups (if not already exists)
        if (!Schema::hasIndex('locales', 'idx_locales_code')) {
            Schema::table('locales', function (Blueprint $table) {
                $table->index('code', 'idx_locales_code');
            });
        }

        // Index for tag name lookups (if not already exists)
        if (!Schema::hasIndex('tags', 'idx_tags_name')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->index('name', 'idx_tags_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE translations DROP INDEX idx_export_performance');
        DB::statement('ALTER TABLE translation_key_tags DROP INDEX idx_tag_filtering');

        if (Schema::hasIndex('locales', 'idx_locales_code')) {
            Schema::table('locales', function (Blueprint $table) {
                $table->dropIndex('idx_locales_code');
            });
        }

        if (Schema::hasIndex('tags', 'idx_tags_name')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropIndex('idx_tags_name');
            });
        }
    }
};
