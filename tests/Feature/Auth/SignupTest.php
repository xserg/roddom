<?php

namespace Tests\Feature\Auth;

use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SignupTest extends TestCase
{
    public function testSuccess(): void
    {
        $response = $this->postJson(route('v1.register'), [
            'email' => 'email@mail.com',
            'password' => 12345678,
            'password_confirmation' => 12345678
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Код отослан на ваш email',
            ]);
    }
}
