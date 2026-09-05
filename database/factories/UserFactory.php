<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'mobile' => '09' . fake()->unique()->numerify('#########'),
            'mobile_verified_at' => now(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /** کاربری که هنوز شماره موبایلش تایید نشده (برای تست فلو ثبت‌نام) */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile_verified_at' => null,
        ]);
    }
}
