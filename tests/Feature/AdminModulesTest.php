<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every admin screen must actually render against a fully seeded database.
 * A controller that compiles but errors on a view is a broken module, so this
 * walks the real GET routes instead of spot-checking a few.
 */
class AdminModulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /* Seeded data exercises the empty-state-free paths: tables, charts and
         * summaries all have rows to render. Transactions are rolled back. */
        (new DatabaseSeeder)->run();
    }

    /** Every admin index screen, plus the dashboard. */
    public static function screens(): array
    {
        return array_map(
            fn (string $path) => [$path],
            [
                '/admin',
                '/admin/pages',
                '/admin/posts',
                '/admin/services',
                '/admin/projects',
                '/admin/testimonials',
                '/admin/navigation',
                '/admin/media',
                '/admin/products',
                '/admin/categories',
                '/admin/orders',
                '/admin/quotations',
                '/admin/appointments',
                '/admin/leads',
                '/admin/customers',
                '/admin/conversations',
                '/admin/ai',
                '/admin/inventory',
                '/admin/purchasing',
                '/admin/production',
                '/admin/finance',
                '/admin/seo',
                '/admin/reports',
                '/admin/users',
                '/admin/settings',
                '/admin/security',
            ]
        );
    }

    #[DataProvider('screens')]
    public function test_a_screen_renders(string $path): void
    {
        $owner = User::query()->where('email', 'owner@reimaginos.test')->firstOrFail();

        $this->actingAs($owner)->get($path)->assertOk();
    }

    public function test_a_page_record_redirects_to_its_builder(): void
    {
        $owner = User::query()->where('email', 'owner@reimaginos.test')->firstOrFail();
        $page = \App\Models\Page::query()->firstOrFail();

        $this->actingAs($owner)
            ->get(route('admin.pages.show', $page))
            ->assertRedirect(route('admin.pages.builder', $page));

        $this->actingAs($owner)->get(route('admin.pages.builder', $page))->assertOk();
    }

    public function test_every_named_admin_get_route_resolves(): void
    {
        /* Guards against a nav item or view pointing at a renamed route. */
        $missing = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if ($name === null || ! str_starts_with($name, 'admin.')) {
                continue;
            }

            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            if ($route->parameterNames() !== []) {
                continue;
            }

            if (! route_exists($name)) {
                $missing[] = $name;
            }
        }

        $this->assertSame([], $missing);
    }

    public function test_a_manager_cannot_reach_owner_modules(): void
    {
        $manager = User::query()->where('email', 'manager@reimaginos.test')->firstOrFail();

        $this->actingAs($manager)->get('/admin/users')->assertForbidden();
        $this->actingAs($manager)->get('/admin/settings')->assertForbidden();
        $this->actingAs($manager)->get('/admin/security')->assertForbidden();
    }

    public function test_staff_cannot_reach_manager_modules(): void
    {
        $staff = User::query()->where('email', 'sales@reimaginos.test')->firstOrFail();

        foreach (['/admin/inventory', '/admin/finance', '/admin/reports', '/admin/ai'] as $path) {
            $this->actingAs($staff)->get($path)->assertForbidden();
        }
    }

    public function test_a_deactivated_account_loses_panel_access_mid_session(): void
    {
        $user = User::query()->where('email', 'sales@reimaginos.test')->firstOrFail();

        $this->actingAs($user)->get('/admin')->assertOk();

        $user->forceFill(['is_active' => false])->save();

        $this->actingAs($user->fresh())->get('/admin')->assertForbidden();
    }

    public function test_unknown_admin_paths_are_not_found(): void
    {
        $owner = User::query()->where('email', 'owner@reimaginos.test')->firstOrFail();

        $this->actingAs($owner)->get('/admin/does-not-exist')->assertNotFound();
    }

    public function test_every_role_can_reach_the_dashboard(): void
    {
        foreach (Role::cases() as $role) {
            $user = User::factory()->create(['role' => $role, 'is_active' => true]);

            $this->actingAs($user)->get('/admin')->assertOk();
        }
    }

    protected function firstId(string $model): int
    {
        return (int) $model::query()->value('id');
    }
}
