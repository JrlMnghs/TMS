<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationKeysSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('translation_keys')->insertOrIgnore([
            ['key_name' => 'auth.login.title', 'description' => 'Login screen title'],
            ['key_name' => 'auth.login.button', 'description' => 'Login button label'],
            ['key_name' => 'auth.logout.button', 'description' => 'Logout button label'],
            ['key_name' => 'onboarding.welcome.title', 'description' => 'Welcome title'],
            ['key_name' => 'onboarding.welcome.subtitle', 'description' => 'Welcome subtitle'],
            ['key_name' => 'errors.network', 'description' => 'Network error message'],
            ['key_name' => 'errors.unknown', 'description' => 'Unknown error message'],
        ]);
    }
}


