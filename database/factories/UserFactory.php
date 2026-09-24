<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'username' => Str::lower(fake()->unique()->bothify('usuario.####??')),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Revendedor con la suscripción al día (vence en un mes, salvo que se indique otra fecha).
     */
    public function reseller(string $plan = 'aliado', mixed $renewsAt = null, string $status = 'active'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => false,
            'is_reseller' => true,
            'reseller_plan' => $plan,
            'subscription_status' => $status,
            'subscription_renews_at' => $renewsAt ?? now()->addMonth()->toDateString(),
        ]);
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
}
