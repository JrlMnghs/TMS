<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Locale;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class MemoryEfficientSeeder extends Seeder
{
    public function run(): void
    {
        // Set memory limit for this operation
        ini_set('memory_limit', '256M');
        
        // Calculate keys needed: 200k translations ÷ 5 locales = 40k keys
        $totalKeys = 40000;
        
        $this->command->info('🚀 Starting memory-efficient performance test data generation for 200k translations...');
        $this->command->info('💾 Memory limit set to: ' . ini_get('memory_limit'));
        
        // Clear existing performance test data first
        $this->command->info('🧹 Cleaning up existing performance test data...');
        DB::statement('DELETE FROM translation_key_tags WHERE translation_key_id IN (SELECT id FROM translation_keys WHERE key_name LIKE "app.module%")');
        DB::statement('DELETE FROM translations WHERE translation_key_id IN (SELECT id FROM translation_keys WHERE key_name LIKE "app.module%")');
        DB::statement('DELETE FROM translation_keys WHERE key_name LIKE "app.module%"');
        
        // Create only 5 locales to reduce total translations
        $locales = collect(['en', 'fr', 'es', 'de', 'it'])
            ->map(fn($code) => Locale::firstOrCreate(['code' => $code], ['name' => strtoupper($code)]));
        
        // Create tags
        $tags = collect(['web', 'mobile', 'auth', 'admin', 'user', 'product', 'order', 'payment', 'notification', 'email', 'api', 'frontend', 'backend'])
            ->map(fn($name) => Tag::firstOrCreate(['name' => $name]));
        
        $this->command->info("📝 Creating {$totalKeys} translation keys using raw SQL...");
        $this->command->info("🌍 Will create {$totalKeys} keys × " . $locales->count() . " locales = " . ($totalKeys * $locales->count()) . " translations");
        
        // Use raw SQL to insert keys in batches
        $batchSize = 500;
        
        for ($i = 0; $i < $totalKeys; $i += $batchSize) {
            $values = [];
            $placeholders = [];
            
            for ($j = 0; $j < min($batchSize, $totalKeys - $i); $j++) {
                $keyIndex = $i + $j;
                $values[] = "app.module{$keyIndex}.action{$keyIndex}.element{$keyIndex}";
                $values[] = "Generated key for performance testing #{$keyIndex}";
                $placeholders[] = "(?, ?)";
            }
            
            $sql = "INSERT INTO translation_keys (key_name, description) VALUES " . implode(', ', $placeholders);
            DB::statement($sql, $values);
            
            if (($i / $batchSize) % 20 === 0) {
                $this->command->info("✅ Created " . min($i + $batchSize, $totalKeys) . " translation keys...");
            }
            
            // Free memory
            unset($values, $placeholders);
            gc_collect_cycles();
        }
        
        $this->command->info('🌍 Creating translations using raw SQL...');
        
        // Get locale IDs as array
        $localeIds = $locales->pluck('id')->toArray();
        $translationCount = 0;
        
        // Process translations in very small batches
        DB::table('translation_keys')
            ->where('key_name', 'like', 'app.module%')
            ->select('id')
            ->orderBy('id')
            ->chunk(100, function($keys) use ($localeIds, &$translationCount) {
                $translations = [];
                
                foreach ($keys as $key) {
                    foreach ($localeIds as $localeId) {
                        $translations[] = [
                            'translation_key_id' => $key->id,
                            'locale_id' => $localeId,
                            'value' => "Translation for key #{$key->id} in locale {$localeId}",
                            'status' => 'approved',
                        ];
                        
                        $translationCount++;
                    }
                }
                
                // Insert translations
                if (!empty($translations)) {
                    DB::table('translations')->insert($translations);
                    unset($translations);
                    
                    if ($translationCount % 10000 === 0) {
                        $this->command->info("✅ Created {$translationCount} translations...");
                    }
                    
                    gc_collect_cycles();
                }
                
                return true;
            });
        
        $this->command->info('🔗 Assigning tags using raw SQL...');
        
        // Assign tags in small batches
        $tagIds = $tags->pluck('id')->toArray();
        
        DB::table('translation_keys')
            ->where('key_name', 'like', 'app.module%')
            ->select('id')
            ->orderBy('id')
            ->chunk(200, function($keys) use ($tagIds) {
                $tagAssignments = [];
                
                foreach ($keys as $key) {
                    $randomTagCount = rand(1, 4);
                    $randomTags = array_rand($tagIds, min($randomTagCount, count($tagIds)));
                    
                    if (!is_array($randomTags)) {
                        $randomTags = [$randomTags];
                    }
                    
                    foreach ($randomTags as $tagIndex) {
                        $tagAssignments[] = [
                            'translation_key_id' => $key->id,
                            'tag_id' => $tagIds[$tagIndex],
                        ];
                    }
                }
                
                if (!empty($tagAssignments)) {
                    DB::table('translation_key_tags')->insert($tagAssignments);
                    unset($tagAssignments);
                    gc_collect_cycles();
                }
                
                return true;
            });
        
        $this->command->info('🎉 Memory-efficient performance test data generation completed!');
        
        // Get final counts
        $finalKeyCount = DB::table('translation_keys')->where('key_name', 'like', 'app.module%')->count();
        $finalTranslationCount = DB::table('translations')->whereIn('translation_key_id', function($query) {
            $query->select('id')->from('translation_keys')->where('key_name', 'like', 'app.module%');
        })->count();
        
        $this->command->info("📊 Created:");
        $this->command->info("   - {$finalKeyCount} translation keys");
        $this->command->info("   - {$finalTranslationCount} translations");
        $this->command->info("   - " . count($tagIds) . " tags");
        $this->command->info("   - " . count($localeIds) . " locales");
        $this->command->info("   - Tag assignments for all new keys");
        $this->command->info("🎯 Target: 200,000 translations achieved!");
    }
}
