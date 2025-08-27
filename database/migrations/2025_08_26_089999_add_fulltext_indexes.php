<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add full-text index for translations value
        DB::statement('ALTER TABLE translations ADD FULLTEXT INDEX ft_translations_value (value)');
        
        // Add full-text index for translation_keys key_name
        DB::statement('ALTER TABLE translation_keys ADD FULLTEXT INDEX ft_translation_keys_key_name (key_name)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE translations DROP INDEX ft_translations_value');
        DB::statement('ALTER TABLE translation_keys DROP INDEX ft_translation_keys_key_name');
    }
};
