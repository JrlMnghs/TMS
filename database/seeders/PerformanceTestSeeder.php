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
        // Set memory limit for this operation
        ini_set('memory_limit', '512M');
        
        $this->command->info('🚀 Starting performance test data generation for 100k+ records...');
        $this->command->info('💾 Memory limit set to: ' . ini_get('memory_limit'));
        
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
        
        // Create translation keys in smaller chunks for better memory management
        $chunkSize = 1000; // Reduced from 5000
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
            
            if (($i / $chunkSize) % 10 === 0) {
                $this->command->info("✅ Created " . min($i + $chunkSize, $totalKeys) . " translation keys...");
            }
            
            // Free memory after each chunk
            unset($keys);
            gc_collect_cycles();
        }
        
        $this->command->info('🌍 Creating translations for each key in all locales...');
        
        // Get the IDs of the newly created keys in smaller batches
        $translationChunkSize = 1000; // Reduced from 10000
        $translationCount = 0;
        
        // Process keys in much smaller chunks to avoid memory issues
        DB::table('translation_keys')
            ->where('key_name', 'like', 'app.module%')
            ->select('id')
            ->orderBy('id')
            ->chunk(500, function($keys) use ($locales, &$translationCount, $translationChunkSize) {
                $translations = [];
                
                foreach ($keys as $key) {
                    foreach ($locales as $locale) {
                        $translations[] = [
                            'translation_key_id' => $key->id,
                            'locale_id' => $locale->id,
                            'value' => "Translation for key #{$key->id} in {$locale->code} language",
                            'status' => 'approved',
                        ];
                        
                        $translationCount++;
                        
                        // Insert in smaller chunks to avoid memory issues
                        if (count($translations) >= $translationChunkSize) {
                            DB::table('translations')->insert($translations);
                            $translations = [];
                            
                            if ($translationCount % 50000 === 0) {
                                $this->command->info("✅ Created {$translationCount} translations...");
                            }
                            
                            // Free memory after each insert
                            gc_collect_cycles();
                        }
                    }
                }
                
                // Insert remaining translations
                if (!empty($translations)) {
                    DB::table('translations')->insert($translations);
                    unset($translations);
                    gc_collect_cycles();
                }
                
                return true; // Continue processing
            });
        
        $this->command->info('🔗 Assigning tags to translation keys...');
        
        // Assign random tags to the newly created translation keys in small chunks
        DB::table('translation_keys')
            ->where('key_name', 'like', 'app.module%')
            ->select('id')
            ->orderBy('id')
            ->chunk(500, function($keys) use ($tags) {
                foreach ($keys as $key) {
                    $randomTags = $tags->random(rand(1, 4))->pluck('id')->toArray();
                    $tagAssignments = [];
                    
                    foreach ($randomTags as $tagId) {
                        $tagAssignments[] = [
                            'translation_key_id' => $key->id,
                            'tag_id' => $tagId,
                        ];
                    }
                    
                    if (!empty($tagAssignments)) {
                        DB::table('translation_key_tags')->insert($tagAssignments);
                        unset($tagAssignments);
                    }
                }
                
                // Free memory after each chunk
                gc_collect_cycles();
                return true;
            });
        
        $this->command->info('🎉 Performance test data generation completed!');
        
        // Get final counts
        $finalKeyCount = DB::table('translation_keys')->where('key_name', 'like', 'app.module%')->count();
        $finalTranslationCount = DB::table('translations')->whereIn('translation_key_id', function($query) {
            $query->select('id')->from('translation_keys')->where('key_name', 'like', 'app.module%');
        })->count();
        
        $this->command->info("📊 Created:");
        $this->command->info("   - {$finalKeyCount} translation keys");
        $this->command->info("   - {$finalTranslationCount} translations");
        $this->command->info("   - " . $tags->count() . " tags");
        $this->command->info("   - " . $locales->count() . " locales");
        $this->command->info("   - Tag assignments for all new keys");
    }
}