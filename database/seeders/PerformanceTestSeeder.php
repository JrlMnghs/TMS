<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Locale;
use App\Models\Tag;
use App\Models\TranslationKey;
use App\Models\Translation;
use Illuminate\Support\Facades\DB;

class PerformanceTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Starting performance test data generation for 100k+ records...');
        
        // Clear existing performance test data first
        $this->command->info('🧹 Cleaning up existing performance test data...');
        DB::table('translation_key_tags')->whereIn('translation_key_id', function($query) {
            $query->select('id')->from('translation_keys')->where('key_name', 'like', 'app.module%');
        })->delete();
        
        DB::table('translations')->whereIn('translation_key_id', function($query) {
            $query->select('id')->from('translation_keys')->where('key_name', 'like', 'app.module%');
        })->delete();
        
        DB::table('translation_keys')->where('key_name', 'like', 'app.module%')->delete();
        
        // Create locales
        $locales = collect(['en', 'fr', 'es', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh'])
            ->map(fn($code) => Locale::firstOrCreate(['code' => $code], ['name' => strtoupper($code)]));
        
        // Create tags
        $tags = collect(['web', 'mobile', 'auth', 'admin', 'user', 'product', 'order', 'payment', 'notification', 'email', 'api', 'frontend', 'backend'])
            ->map(fn($name) => Tag::firstOrCreate(['name' => $name]));
        
        $this->command->info('📝 Creating 100,000 translation keys...');
        
        // Create translation keys in chunks for better performance
        $chunkSize = 5000;
        $totalKeys = 100000;
        
        for ($i = 0; $i < $totalKeys; $i += $chunkSize) {
            $keys = [];
            for ($j = 0; $j < min($chunkSize, $totalKeys - $i); $j++) {
                $keyIndex = $i + $j;
                $keys[] = [
                    'key_name' => "app.module{$keyIndex}.action{$keyIndex}.element{$keyIndex}",
                    'description' => "Generated key for performance testing #{$keyIndex}",
                ];
            }
            
            DB::table('translation_keys')->insert($keys);
            
            if (($i / $chunkSize) % 5 === 0) {
                $this->command->info("✅ Created " . min($i + $chunkSize, $totalKeys) . " translation keys...");
            }
        }
        
        $this->command->info('🌍 Creating translations for each key in all locales...');
        
        // Get the IDs of the newly created keys (keys with app.module prefix)
        $newKeyIds = DB::table('translation_keys')
            ->where('key_name', 'like', 'app.module%')
            ->pluck('id')
            ->toArray();
        
        $translationChunkSize = 10000;
        $translationCount = 0;
        
        // Process keys in chunks to avoid memory issues
        $keyChunks = array_chunk($newKeyIds, 1000);
        
        foreach ($keyChunks as $keyChunk) {
            $translations = [];
            
            foreach ($keyChunk as $keyId) {
                foreach ($locales as $locale) {
                    $translations[] = [
                        'translation_key_id' => $keyId,
                        'locale_id' => $locale->id,
                        'value' => "Translation for key #{$keyId} in {$locale->code} language",
                        'status' => 'approved',
                    ];
                    
                    $translationCount++;
                    
                    // Insert in chunks to avoid memory issues
                    if (count($translations) >= $translationChunkSize) {
                        DB::table('translations')->insert($translations);
                        $translations = [];
                        
                        if ($translationCount % 100000 === 0) {
                            $this->command->info("✅ Created {$translationCount} translations...");
                        }
                    }
                }
            }
            
            // Insert remaining translations
            if (!empty($translations)) {
                DB::table('translations')->insert($translations);
            }
        }
        
        $this->command->info('🔗 Assigning tags to translation keys...');
        
        // Assign random tags to the newly created translation keys
        foreach (array_chunk($newKeyIds, 1000) as $keyChunk) {
            foreach ($keyChunk as $keyId) {
                $randomTags = $tags->random(rand(1, 4))->pluck('id')->toArray();
                $tagAssignments = [];
                
                foreach ($randomTags as $tagId) {
                    $tagAssignments[] = [
                        'translation_key_id' => $keyId,
                        'tag_id' => $tagId,
                    ];
                }
                
                if (!empty($tagAssignments)) {
                    DB::table('translation_key_tags')->insert($tagAssignments);
                }
            }
        }
        
        $this->command->info('🎉 Performance test data generation completed!');
        $this->command->info("📊 Created:");
        $this->command->info("   - " . count($newKeyIds) . " translation keys");
        $this->command->info("   - " . $translationCount . " translations");
        $this->command->info("   - " . $tags->count() . " tags");
        $this->command->info("   - " . $locales->count() . " locales");
        $this->command->info("   - Tag assignments for all new keys");
    }
}