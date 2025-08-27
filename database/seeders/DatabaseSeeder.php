<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users for API authentication
        User::factory(3)->create();
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Domain seeders
        $this->call([
            LocalesSeeder::class,
            TagsSeeder::class,
            TranslationKeysSeeder::class,
            TranslationsSeeder::class,
            TranslationKeyTagsSeeder::class,
        ]);
    }
}
