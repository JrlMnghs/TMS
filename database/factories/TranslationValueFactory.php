<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TranslationValue;
use App\Models\TranslationKey;
use App\Models\Locale;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TranslationValue>
 */
class TranslationValueFactory extends Factory
{
    protected $model = TranslationValue::class;

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
        ];
    }
}
