<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\University>
 */
class UniversityFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company . ' University';
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'city' => $this->faker->city,
            'province' => $this->faker->state,
            'website' => $this->faker->url,
        ];
    }
}
