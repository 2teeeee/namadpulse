<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->firstOrFail();
        $traderRole = Role::where('name', Role::TRADER)->firstOrFail();

        $admin = User::updateOrCreate(
            ['mobile' => '09120000000'],
            [
                'name' => 'مدیر سیستم',
                'mobile_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // چند کاربر تستی معامله‌گر برای توسعه محلی
        User::factory()
            ->count(5)
            ->create()
            ->each(fn (User $user) => $user->roles()->syncWithoutDetaching([$traderRole->id]));
    }
}
