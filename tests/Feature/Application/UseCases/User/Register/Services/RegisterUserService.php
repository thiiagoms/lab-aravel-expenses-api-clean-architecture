<?php

namespace Tests\Feature\Application\UseCases\User\Register\Services;

use Tests\TestCase;

class RegisterUserService extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
