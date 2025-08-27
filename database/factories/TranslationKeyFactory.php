<?php

namespace Database\Factories;

use App\Models\TranslationKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TranslationKey>
 */
class TranslationKeyFactory extends Factory
{
    protected $model = TranslationKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key_name' => $this->faker->unique()->slug(3) . '.' . $this->faker->word() . '.' . $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create auth-related translation keys.
     */
    public function auth(): static
    {
        return $this->state(fn (array $attributes) => [
            'key_name' => 'auth.' . $this->faker->word() . '.' . $this->faker->word(),
        ]);
    }

    /**
     * Create web-related translation keys.
     */
    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'key_name' => 'web.' . $this->faker->word() . '.' . $this->faker->word(),
        ]);
    }

    /**
     * Create admin-related translation keys.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'key_name' => 'admin.' . $this->faker->word() . '.' . $this->faker->word(),
        ]);
    }
}
