<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('locales')->insertOrIgnore([
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'ja', 'name' => 'Japanese'],
        ]);
    }
}


