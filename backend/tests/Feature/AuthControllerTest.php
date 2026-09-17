<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function seedUsers(): User
    {
        return User::factory()->create([
            'email' => 'alice@example.local',
            'password' => Hash::make('LabPass123!'),
            'role' => 'user',
            'is_disabled' => false,
        ]);
    }

    protected function login(array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/auth/login', array_merge([
            'email' => 'alice@example.local',
            'password' => 'LabPass123!',
        ], $extra));
    }

    public function test_login_returns_session_cookie(): void
    {
        $this->seedUsers();
        $response = $this->login();
        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'session']);
        $this->assertNotNull($response->headers->get('Set-Cookie'));
    }

    public function test_login_reuses_pre_auth_session_in_vulnerable_mode(): void
    {
        // SM-02: session fixation
        putenv('VULN_SESSION_FIXATION=true');
        $user = $this->seedUsers();

        // Create a pre-auth session and set it as cookie
        $preAuth = \App\Models\Session::create([
            'id' => 'preauth-123456',
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'last_activity' => now(),
            'is_current' => true,
        ]);

        $response = $this->withCookie('sessionvault_session', 'preauth-123456')
            ->login();

        $response->assertStatus(200);
        // In vulnerable mode, the session id should be reused
        $this->assertEquals('preauth-123456', $response->json('session.id'));
    }

    public function test_login_creates_new_session_when_no_pre_auth(): void
    {
        $this->seedUsers();
        $response = $this->login();
        $this->assertMatchesRegularExpression(
            '/^session-\d{6}$/',
            $response->json('session.id')
        );
    }

    public function test_cookie_includes_httponly_in_vulnerable_mode(): void
    {
        // SM-04: missing HttpOnly
        putenv('VULN_MISSING_HTTPONLY=true');
        $this->seedUsers();
        $response = $this->login();
        $cookie = $response->headers->get('Set-Cookie');
        // In vulnerable mode, HttpOnly should be absent
        $this->assertStringNotContainsString('HttpOnly', $cookie);
    }

    public function test_cookie_includes_secure_in_secure_mode(): void
    {
        putenv('VULN_MISSING_SECURE=false');
        $this->seedUsers();
        $response = $this->login();
        $cookie = $response->headers->get('Set-Cookie');
        $this->assertStringContainsString('Secure', $cookie);
    }

    public function test_logout_in_vulnerable_mode_leaves_session_valid(): void
    {
        // SM-08: logout does not invalidate server session
        putenv('VULN_LOGOUT_NOT_INVALIDATE=true');
        $user = $this->seedUsers();
        $session = \App\Models\Session::create([
            'id' => SessionService::generateSessionId(),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'last_activity' => now(),
            'is_current' => true,
        ]);

        $response = $this->withCookie('sessionvault_session', $session->id)
            ->postJson('/api/auth/logout');
        $response->assertStatus(200);
        // Session still exists in vulnerable mode
        $this->assertNotNull(\App\Models\Session::find($session->id));
    }

    public function test_password_change_in_vulnerable_mode_survives_sessions(): void
    {
        // SM-11: password change does not invalidate sessions
        putenv('VULN_PASSWORD_CHANGE_INVALIDATION=true');
        $user = $this->seedUsers();
        $session = \App\Models\Session::create([
            'id' => SessionService::generateSessionId(),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'last_activity' => now(),
            'is_current' => true,
        ]);

        $this->withCookie('sessionvault_session', $session->id)
            ->postJson('/api/auth/change-password', [
                'current_password' => 'LabPass123!',
                'new_password' => 'NewPass123!',
                'new_password_confirmation' => 'NewPass123!',
            ])->assertStatus(200);

        $this->assertNotNull(\App\Models\Session::find($session->id));
    }
}