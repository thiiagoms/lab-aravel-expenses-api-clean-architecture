<?php

namespace Database\Factories\Infrastructure\Framework\Laravel\Persistence;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\User\Status\Implementations\Active;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;
use Src\Infrastructure\Framework\Laravel\Persistence\User as LaravelUserModel;

/**
 * @extends Factory<LaravelUserModel>
 */
class UserFactory extends Factory
{
    protected $model = LaravelUserModel::class;

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
            'id' => new Id(fake()->uuid),
            'name' => new Name(fake()->name()),
            'email' => new Email(fake()->unique()->safeEmail()),
            'password' => new Password('@p5sSw0rd!'),
            'role' => Role::USER,
            'status' => new Active,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
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
}
