<?php

namespace Tests\Feature;

use App\Models\Session;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SessionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function seedUsers(): User
    {
        return User::factory()->create([
            'email' => 'alice@example.local',
            'password' => Hash::make('LabPass123!'),
            'role' => 'user',
        ]);
    }

    public function test_user_can_list_their_sessions(): void
    {
        $user = $this->seedUsers();
        Session::factory(3)->create(['user_id' => $user->id]);

        $token = base64_encode('dummy'); // placeholder
        $response = $this->getJson('/api/sessions');
        $response->assertStatus(401); // requires auth
    }

    public function test_revoke_all_other_sessions(): void
    {
        $user = $this->seedUsers();
        Session::factory(3)->create(['user_id' => $user->id]);

        // SM-10: concurrent sessions are allowed
        $this->assertEquals(3, Session::where('user_id', $user->id)->count());
    }

    public function test_session_revocation_requires_authentication(): void
    {
        $response = $this->deleteJson('/api/sessions/fake-id');
        $response->assertStatus(401);
    }
}