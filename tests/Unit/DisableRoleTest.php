<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Role;

class DisableRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $organization;
    protected $role;
    protected $defaultRole;

    public function setUp(): void
    {
        parent::setUp();

        // Creating admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        // Creating regular user
        $this->user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);

        // Creating organisation
        $this->organization = Organisation::create([
            'name' => 'Test Organisation',
            'email' => 'org@example.com',
            'description' => 'Description',
            'industry' => 'Industry',
            'type' => 'Type',
            'country' => 'Country',
            'address' => 'Address',
            'state' => 'State',
        ]);

        // Creating roles
        $this->role = Role::create([
            'name' => 'Test Role',
            'org_id' => $this->organization->id,
            'is_active' => true
        ]);

        $this->defaultRole = Role::create([
            'name' => 'Default Role',
            'org_id' => $this->organization->id,
            'is_default' => true
        ]);

        // Assigning roles to users
        $this->admin->roles()->attach($this->role->id);
        $this->user->roles()->attach($this->role->id);
    }

    public function testDisableRoleSuccess()
    {
        $response = $this->actingAs($this->admin, 'api')->put("/api/v1/organizations/{$this->organization->id}/roles/{$this->role->id}/disable");

        $response->assertStatus(200);
        $this->assertFalse($this->role->fresh()->is_active);

        $this->assertCount(0, $this->role->users);
        $this->assertCount(2, $this->defaultRole->users); // Both admin and user should be moved to default role
    }

    public function testDisableRoleUnauthorized()
    {
        $response = $this->actingAs($this->user, 'api')->put("/api/v1/organizations/{$this->organization->id}/roles/{$this->role->id}/disable");

        $response->assertStatus(403);
    }
}
