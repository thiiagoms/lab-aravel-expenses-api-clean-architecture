<?php

namespace Tests\Unit\Domain\Auth\ValueObjects;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Domain\Auth\ValueObjects\Token;

class TokenTest extends TestCase
{
    #[Test]
    public function it_should_create_token(): void
    {
        $token = new Token('jwt.token.string', 'Bearer', 3600);

        $this->assertEquals('jwt.token.string', $token->token());
        $this->assertEquals('Bearer', $token->type());
        $this->assertEquals(3600, $token->expiresIn());
    }

    #[Test]
    public function it_should_parse_token_data_into_array(): void
    {
        $token = new Token('jwt.token.string', 'Bearer', 3600);

        $expectedData = [
            'token' => 'jwt.token.string',
            'type' => 'Bearer',
            'expiresIn' => 3600,
        ];

        $this->assertSame($expectedData, $token->toArray());
    }

    #[Test]
    public function it_should_throw_exception_when_token_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'token' cannot be empty.");

        new Token('', 'Bearer', 3600);
    }

    #[Test]
    public function it_should_throw_exception_when_type_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'type' cannot be empty.");

        new Token('jwt.token.string', '', 3600);
    }

    #[Test]
    public function it_should_throw_exception_when_expires_in_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Property 'expiresIn' cannot be empty.");

        new Token('jwt.token.string', 'Bearer', 0);
    }
}
