<?php

namespace Database\Factories;

use App\Models\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Locale>
 */
class LocaleFactory extends Factory
{
    protected $model = Locale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->languageCode();
        
        return [
            'code' => $code,
            'name' => strtoupper($code),
        ];
    }

    /**
     * Create a specific locale.
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'en',
            'name' => 'ENGLISH',
        ]);
    }

    /**
     * Create a French locale.
     */
    public function french(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'fr',
            'name' => 'FRENCH',
        ]);
    }

    /**
     * Create a Spanish locale.
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'es',
            'name' => 'SPANISH',
        ]);
    }
}
