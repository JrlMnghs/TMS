<?php

namespace Database\Factories;

use App\Models\Locale;
use App\Models\Translation;
use App\Models\TranslationKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translation_key_id' => TranslationKey::factory(),
            'locale_id' => Locale::factory(),
            'value' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['draft', 'approved']),
        ];
    }

    /**
     * Create an approved translation.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Create a draft translation.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Create a translation for a specific locale.
     */
    public function forLocale(Locale $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale_id' => $locale->id,
        ]);
    }

    /**
     * Create a translation for a specific translation key.
     */
    public function forKey(TranslationKey $key): static
    {
        return $this->state(fn (array $attributes) => [
            'translation_key_id' => $key->id,
        ]);
    }
}