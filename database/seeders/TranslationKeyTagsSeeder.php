<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationKeyTagsSeeder extends Seeder
{
    public function run(): void
    {
        $tagIds = DB::table('tags')->pluck('id', 'name');
        $keyIds = DB::table('translation_keys')->pluck('id', 'key_name');

        $pairs = [
            ['auth.login.title', 'auth'],
            ['auth.login.button', 'auth'],
            ['auth.logout.button', 'auth'],
            ['onboarding.welcome.title', 'onboarding'],
            ['onboarding.welcome.subtitle', 'onboarding'],
            ['errors.network', 'errors'],
            ['errors.unknown', 'errors'],
        ];

        $rows = [];
        foreach ($pairs as [$keyName, $tagName]) {
            $keyId = $keyIds[$keyName] ?? null;
            $tagId = $tagIds[$tagName] ?? null;
            if ($keyId && $tagId) {
                $rows[] = [
                    'translation_key_id' => $keyId,
                    'tag_id' => $tagId,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('translation_key_tags')->insertOrIgnore($rows);
        }
    }
}


