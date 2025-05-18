<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserDashboardPreferenceFactory extends Factory
{
    protected $model = UserDashboardPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'accent_color' => $this->faker->hexColor(),
            'background_image_path' => 'dashboard_backgrounds/' . $this->faker->md5 . '.webp',
        ];
    }
}
