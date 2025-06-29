<?php

namespace Src\Infrastructure\Framework\Laravel\Persistence;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Src\Domain\Expense\Status\Enums\Status;
use Src\Domain\Expense\Status\Factory\StatusFactory;
use Src\Domain\Expense\Status\Interfaces\StatusInterface;
use Src\Domain\Expense\ValueObjects\Amount;
use Src\Domain\Expense\ValueObjects\Description;
use Src\Domain\ValueObjects\Id;

class Expense extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'amount',
        'description',
        'status',
    ];

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn (string $id): Id => new Id($id),
            set: fn (?Id $id = null): string => $id?->getValue() ?? Str::uuid()->toString()
        );
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn (int $amount): Amount => new Amount($amount)
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (string $description): Description => new Description($description)
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (string $status): StatusInterface => StatusFactory::build(Status::from($status)),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
