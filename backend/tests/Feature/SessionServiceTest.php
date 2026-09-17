<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_id_is_predictable_in_vulnerable_mode(): void
    {
        // SM-01: predictable session IDs
        $id = SessionService::generateSessionId();
        $this->assertMatchesRegularExpression('/^session-\d{6}$/', $id);
    }

    public function test_session_id_is_random_in_secure_mode(): void
    {
        $this->app['config']->set('app.env', 'testing');
        // Force secure mode via env
        putenv('VULN_PREDICTABLE_SESSION=false');
        $id = SessionService::generateSessionId();
        $this->assertMatchesRegularExpression('/^sess_[a-f0-9]{32}$/', $id);
    }

    public function test_session_creation_increments_predictable_id(): void
    {
        User::factory()->create();
        $first = SessionService::generateSessionId();
        SessionService::createSession(User::first(), request());
        $second = SessionService::generateSessionId();
        $this->assertNotEquals($first, $second);
    }

    public function test_session_rotation_in_vulnerable_mode(): void
    {
        // SM-03: no rotation
        putenv('VULN_NO_SESSION_ROTATION=true');
        $user = User::factory()->create();
        $session = SessionService::createSession($user, request());
        $rotated = SessionService::rotateSession($user, $session->id, request());
        $this->assertEquals($session->id, $rotated->id);
    }

    public function test_session_rotation_in_secure_mode(): void
    {
        putenv('VULN_NO_SESSION_ROTATION=false');
        $user = User::factory()->create();
        $session = SessionService::createSession($user, request());
        $rotated = SessionService::rotateSession($user, $session->id, request());
        $this->assertNotEquals($session->id, $rotated->id);
    }

    public function test_invalidate_removes_session(): void
    {
        $user = User::factory()->create();
        $session = SessionService::createSession($user, request());
        $result = SessionService::invalidate($session->id);
        $this->assertTrue($result);
        $this->assertNull(\App\Models\Session::find($session->id));
    }

    public function test_invalidate_all_for_user_removes_all_sessions(): void
    {
        $user = User::factory()->create();
        SessionService::createSession($user, request());
        SessionService::createSession($user, request());
        $count = SessionService::invalidateAllForUser($user->id);
        $this->assertGreaterThanOrEqual(2, $count);
    }
}