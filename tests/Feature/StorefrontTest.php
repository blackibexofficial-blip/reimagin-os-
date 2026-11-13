<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

/**
 * The public site is the shop window: every route must render for an anonymous
 * visitor, and the buying path must work end to end against seeded data.
 */
class StorefrontTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (new DatabaseSeeder)->run();
    }

    public function test_homepage_renders(): void
    {
        $this->get('/')->assertOk()->assertSee(settings('site_name', 'Reimagin'), false);
    }

    public function test_primary_pages_render(): void
    {
        foreach (['/shop', '/journal', '/services', '/projects', '/contact', '/cart'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_a_product_page_renders(): void
    {
        $product = Product::query()->published()->firstOrFail();

        $this->get(route('site.products.show', $product))
            ->assertOk()
            ->assertSee($product->name, false);
    }

    public function test_a_project_page_renders(): void
    {
        $project = \App\Models\ShowcaseProject::query()->where('is_published', true)->firstOrFail();

        $this->get(route('site.projects.show', $project))->assertOk();
    }

    public function test_a_blog_post_renders(): void
    {
        $post = \App\Models\BlogPost::query()->where('status', 'published')->firstOrFail();

        $this->get(route('site.blog.show', $post))->assertOk();
    }

    public function test_a_cms_page_renders_through_the_catch_all(): void
    {
        $page = \App\Models\Page::query()->where('status', 'published')->where('is_homepage', false)->firstOrFail();

        $this->get('/'.$page->slug)->assertOk();
    }

    public function test_the_catch_all_does_not_shadow_real_routes(): void
    {
        /* A page whose slug collides with a real route must lose to the route. */
        \App\Models\Page::query()->create([
            'title' => 'Shop duplicate',
            'slug' => 'shop',
            'status' => 'published',
            'content' => [],
            'published_content' => [],
        ]);

        $this->get('/shop')->assertOk()->assertSee('Shop', false);
    }

    public function test_cart_and_checkout_place_a_real_order(): void
    {
        $product = Product::query()->published()->where('track_stock', true)->where('stock_quantity', '>', 2)->firstOrFail();
        $before = \App\Models\Order::query()->count();

        $this->post(route('site.cart.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect(route('site.cart'));

        $this->get(route('site.cart'))
            ->assertOk()
            ->assertSee($product->name, false);

        $this->post(route('site.checkout.store'), [
            'name' => 'Nadia Malik',
            'email' => 'nadia@example.test',
            'phone' => '+92 300 555 1234',
            'address_line_1' => '44-C Model Town',
            'city' => 'Lahore',
            'notes' => 'Please call before delivery.',
        ])->assertRedirect();

        $this->assertSame($before + 1, \App\Models\Order::query()->count());

        $order = \App\Models\Order::query()->latest('id')->firstOrFail();

        $this->assertSame('Nadia Malik', $order->customer->name);
        $this->assertGreaterThan(0, $order->total_minor);
        $this->assertSame(2, $order->items()->sum('quantity'));

        $this->get(route('site.order', $order->reference))
            ->assertOk()
            ->assertSee($order->reference, false);
    }

    public function test_contact_form_creates_a_lead(): void
    {
        $before = \App\Models\Lead::query()->count();

        $this->post(route('site.contact.store'), [
            'name' => 'Imran Sheikh',
            'email' => 'imran@example.test',
            'message' => 'We need a dining table for eight.',
        ])->assertRedirect();

        $this->assertSame($before + 1, \App\Models\Lead::query()->count());
        $this->assertSame('new', \App\Models\Lead::query()->latest('id')->value('status'));
    }

    public function test_booking_requires_a_phone_number(): void
    {
        $this->post(route('site.appointments.store'), [
            'name' => 'Imran Sheikh',
            'type' => 'showroom',
            'starts_at' => now()->addDays(3)->setTime(12, 0)->toDateTimeString(),
        ])->assertSessionHasErrors('phone');
    }

    public function test_the_admin_panel_is_not_reachable_from_the_storefront_catch_all(): void
    {
        $this->get('/admin')->assertRedirect('/login');

        $user = User::query()->where('email', 'sales@reimaginos.test')->firstOrFail();
        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_unknown_addresses_are_not_found(): void
    {
        $this->get('/this-page-does-not-exist')->assertNotFound();
    }
}
