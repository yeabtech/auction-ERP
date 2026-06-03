<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $bidderUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'bidder']);

        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super_admin');

        $this->bidderUser = User::factory()->create();
        $this->bidderUser->assignRole('bidder');
    }

    /** @test */
    public function unauthenticated_users_are_denied()
    {
        $response = $this->get('/admin/dashboard');

        // Laravel auth middleware by default redirects to '/login'
        $response->assertRedirect('/login');
    }

    /** @test */
    public function users_without_correct_role_get_forbidden()
    {
        $response = $this->actingAs($this->bidderUser)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function users_with_correct_role_can_access()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/dashboard');

        $response->assertStatus(200);
    }
}
