<?php
declare(strict_types=1);

namespace Http\Controllers\User;

use App\Dto\User\LoginDto;
use App\Models\LoginCode;
use App\Models\User;
use App\Services\UserService;
use Tests\TestCase;

class LoginCodeControllerTest extends TestCase
{
    private ?User $user;
    private ?LoginCode $loginCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->assertTrue(app(UserService::class)
            ->login(new LoginDto($this->user->email, 'password')));

        $this->loginCode = LoginCode::firstWhere('email', $this->user->email);
    }

    public function testSuccess()
    {
        $payload = ['code' => $this->loginCode->code];

        $this->post(route('v1.login.code'), $payload)
            ->assertOk()
            ->assertJsonStructure([
                'user',
                'access_token',
                'refresh_token',
                'token_type',
            ]);
    }

    public function testSuccessWithDeviceName()
    {
        $payload = ['code' => $this->loginCode->code, 'device_name' => 'valid_device_name'];

        $this->post(route('v1.login.code'), $payload)
            ->assertOk()
            ->assertJsonStructure([
                'user',
                'access_token',
                'refresh_token',
                'token_type',
            ]);
    }

    public function testInvalidCode()
    {
        $payload = ['code' => 'invalid-code'];

        $this->post(route('v1.login.code'), $payload)
            ->assertFound();
    }

    public function testDontSendCode()
    {
        $payload = ['device_name' => 'valid_device_name'];

        $this->post(route('v1.login.code'), $payload)
            ->assertFound();
    }

    public function testDontSendPayload()
    {
        $payload = [];

        $this->post(route('v1.login.code'), $payload)
            ->assertFound();
    }
}
