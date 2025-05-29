<?php

declare(strict_types=1);

namespace Src\Infrastructure\Framework\Laravel\Persistence;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\Infrastructure\Framework\Laravel\Persistence\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Src\Domain\User\Role\Enums\Role;
use Src\Domain\User\Status\Enums\Status;
use Src\Domain\User\Status\Factory\StatusFactory;
use Src\Domain\User\Status\Interfaces\StatusInterface;
use Src\Domain\User\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Name;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\ValueObjects\Id;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn (string $id): Id => new Id($id),
            set: fn (?Id $id = null): string => $id?->getValue() ?? Str::uuid()->toString()
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $name): Name => new Name($name),
            set: fn (Name $name): string => $name->getValue()
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $email): Email => new Email($email),
            set: fn (Email $email): string => $email->getValue()
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn (string $password): Password => new Password(password: $password, hashed: false),
            set: fn (Password $password): string => $password->getValue()
        );
    }

    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn (string $role): Role => Role::from($role),
            set: fn (Role $role): string => $role->value
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (string $status): StatusInterface => StatusFactory::build(Status::from($status)),
            set: fn (StatusInterface $status): string => $status->getStatus()->value
        );
    }
}
