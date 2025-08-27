<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tags')->insertOrIgnore([
            ['name' => 'web'],
            ['name' => 'mobile'],
            ['name' => 'auth'],
            ['name' => 'onboarding'],
            ['name' => 'errors'],
        ]);
    }
}


