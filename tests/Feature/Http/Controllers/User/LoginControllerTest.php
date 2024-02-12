<?php
declare(strict_types=1);

namespace Http\Controllers\User;

use App\Models\LoginCode;
use App\Models\User;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    private ?User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function testSuccess()
    {
        $payload = ['email' => $this->user->email, 'password' => 'password'];

        $response = $this->post(route('v1.login'), $payload);

        $response->assertOk()
            ->assertExactJson(['message' => 'Код отослан на ваш email']);

        $this->assertDatabaseHas('login_codes', ['email' => $this->user->email]);
        $code = LoginCode::firstWhere('email', $this->user->email);
        $this->assertTrue($code->created_at->isPast());
        $this->assertTrue($code->created_at->addMinute()->isFuture());
    }

    public function testInvalidPassword()
    {
        $payload = ['email' => $this->user->email, 'password' => 'invalid password'];

        $this->post(route('v1.login'), $payload)
            ->assertStatus(401)
            ->assertExactJson([
                "message" => "Email or password is invalid",
                "errors" => ["email" => ["Email or password is invalid"], "password" => ["Email or password is invalid"]]
            ]);

        $this->assertDatabaseMissing('login_codes', ['email' => $this->user->email]);
    }

    public function testInvalidEmail()
    {
        $payload = ['email' => 'invalid-email@mail.com', 'password' => 'password'];

        $this->post(route('v1.login'), $payload)
            ->assertStatus(401)
            ->assertExactJson([
                "message" => "Email or password is invalid",
                "errors" => ["email" => ["Email or password is invalid"], "password" => ["Email or password is invalid"]]
            ]);

        $this->assertDatabaseMissing('login_codes', ['email' => $this->user->email]);
    }
}
