<?php

namespace Database\Factories;

use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    public function definition(): array
    {
        return [
            'notice_method' => $this->faker->randomElement(['SMS', 'E-mail', 'Both']),
            'city' => $this->faker->city,
            'meteorological_warnings' => $this->faker->boolean,
            'hydrological_warnings' => $this->faker->boolean,
            'air_quality_warnings' => $this->faker->boolean,
            'temperature_warning' => $this->faker->boolean,
            'temperature_check_value' => $this->faker->randomFloat(2, -20, 40),
        ];
    }
}
