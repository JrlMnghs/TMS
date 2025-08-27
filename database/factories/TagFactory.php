<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }

    /**
     * Create common tags for testing.
     */
    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'web',
        ]);
    }

    public function auth(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'auth',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'mobile',
        ]);
    }
}
