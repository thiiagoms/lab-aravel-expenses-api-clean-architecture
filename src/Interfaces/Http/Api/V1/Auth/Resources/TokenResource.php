<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\Auth\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Domain\Auth\ValueObjects\Token;

class TokenResource extends JsonResource
{
    public function toArray(Request $request): array|\JsonSerializable|Arrayable
    {
        /** @var Token $token */
        $token = $this->resource;

        return [
            'token' => $token->token(),
            'type' => $token->type(),
            'expires_in' => $token->expiresIn(),
        ];
    }
}
