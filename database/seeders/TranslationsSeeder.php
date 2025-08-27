<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $localeIds = DB::table('locales')->pluck('id', 'code');
        $keyIds = DB::table('translation_keys')->pluck('id', 'key_name');

        $data = [];

        $values = [
            'auth.login.title' => [
                'en' => 'Login',
                'fr' => 'Connexion',
                'es' => 'Iniciar sesión',
                'de' => 'Anmelden',
                'ja' => 'ログイン',
            ],
            'auth.login.button' => [
                'en' => 'Sign In',
                'fr' => 'Se connecter',
                'es' => 'Acceder',
                'de' => 'Einloggen',
                'ja' => 'サインイン',
            ],
            'auth.logout.button' => [
                'en' => 'Sign Out',
                'fr' => 'Se déconnecter',
                'es' => 'Cerrar sesión',
                'de' => 'Abmelden',
                'ja' => 'サインアウト',
            ],
            'onboarding.welcome.title' => [
                'en' => 'Welcome',
                'fr' => 'Bienvenue',
                'es' => 'Bienvenido',
                'de' => 'Willkommen',
                'ja' => 'ようこそ',
            ],
            'onboarding.welcome.subtitle' => [
                'en' => 'Let’s get started',
                'fr' => 'Commençons',
                'es' => 'Empecemos',
                'de' => 'Lass uns anfangen',
                'ja' => '始めましょう',
            ],
            'errors.network' => [
                'en' => 'Network error. Please try again.',
                'fr' => 'Erreur réseau. Veuillez réessayer.',
                'es' => 'Error de red. Inténtalo de nuevo.',
                'de' => 'Netzwerkfehler. Bitte versuche es erneut.',
                'ja' => 'ネットワーク エラー。 もう一度やり直してください。',
            ],
            'errors.unknown' => [
                'en' => 'Something went wrong.',
                'fr' => 'Quelque chose a mal tourné.',
                'es' => 'Algo salió mal.',
                'de' => 'Etwas ist schief gelaufen.',
                'ja' => '問題が発生しました。',
            ],
        ];

        foreach ($values as $keyName => $localized) {
            $keyId = $keyIds[$keyName] ?? null;
            if ($keyId === null) {
                continue;
            }
            foreach ($localized as $code => $text) {
                $localeId = $localeIds[$code] ?? null;
                if ($localeId === null) {
                    continue;
                }
                $data[] = [
                    'translation_key_id' => $keyId,
                    'locale_id' => $localeId,
                    'value' => $text,
                    'status' => 'approved',
                ];
            }
        }

        if (!empty($data)) {
            DB::table('translations')->insertOrIgnore($data);
        }
    }
}


