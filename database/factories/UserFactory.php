<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'is_admin' => false, // Default to non-admin for safety
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->optional()->phoneNumber(),
            'avatar_path' => null,
            'user_status' => match (true) {
                ($r = fake()->numberBetween(1, 100)) <= 95 => 'active', // 95% chance
                $r <= 98 => 'restricted', // 3% chance
                default => 'suspended', // 2% chance
            },
            // 'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'user_status' => 'suspended',
        ]);
    }
}
