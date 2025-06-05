<?php

declare(strict_types=1);

namespace Src\Interfaces\Http\Api\V1\User\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Domain\User\Entities\User;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array|\JsonSerializable|Arrayable
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id()->getValue(),
            'name' => $user->name()->getValue(),
            'email' => $user->email()->getValue(),
            'created_at' => $user->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
