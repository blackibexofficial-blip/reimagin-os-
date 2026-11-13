<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /** The password every factory user shares in tests and local seeding. */
    public const PASSWORD = 'password';

    public function definition(): array
    {
        static $sequence = 0;
        $sequence++;

        return [
            'name' => 'Studio User '.$sequence,
            'email' => 'user'.$sequence.'@reimaginos.test',
            'email_verified_at' => now(),
            'password' => Hash::make(self::PASSWORD),
            'role' => Role::Staff,
            'is_active' => true,
        ];
    }

    public function owner(): static
    {
        return $this->state(fn () => ['role' => Role::Owner]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => Role::Admin]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
