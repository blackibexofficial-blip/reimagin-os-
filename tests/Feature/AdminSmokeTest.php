<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    public function test_a_staff_member_can_reach_the_dashboard(): void
    {
        $user = User::factory()->create(['role' => Role::Manager, 'is_active' => true]);

        $this->actingAs($user)->get('/admin')->assertOk()->assertSee('Studio overview');
    }

    public function test_inactive_accounts_are_rejected(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_staff_member_cannot_open_owner_only_modules(): void
    {
        $user = User::factory()->create(['role' => Role::Staff, 'is_active' => true]);

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_an_active_user_can_sign_in(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
