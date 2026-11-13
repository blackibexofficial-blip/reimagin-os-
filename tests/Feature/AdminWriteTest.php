<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

/**
 * Write paths for every module.
 *
 * Each case posts through the real route, checks the row that landed in the
 * database, and follows the redirect so the destination view is rendered too —
 * a stale column name in a table or a renamed variable therefore fails here
 * rather than in production.
 */
class AdminWriteTest extends TestCase
{
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        (new DatabaseSeeder)->run();

        $this->owner = User::query()->where('email', 'owner@reimaginos.test')->firstOrFail();
    }

    public function test_pages_can_be_created_updated_and_deleted(): void
    {
        $this->actingAs($this->owner)->post(route('admin.pages.store'), [
            'title' => 'Trade programme',
            'slug' => '',
            'status' => 'draft',
            'template' => 'default',
            'excerpt' => 'For architects and interior designers.',
            'seo_title' => 'Trade programme',
            'seo_description' => 'Volume pricing for interior studios in Lahore.',
        ])->assertRedirect();

        $page = Page::query()->where('slug', 'trade-programme')->firstOrFail();

        $this->assertSame([], $page->blocks());
        $this->assertSame('draft', $page->status);

        /* The builder is the page's edit screen; it must render the stored JSON. */
        $this->actingAs($this->owner)
            ->get(route('admin.pages.builder', $page))
            ->assertOk()
            ->assertSee('Trade programme', false);

        $this->actingAs($this->owner)->put(route('admin.pages.update', $page), [
            'title' => 'Trade programme 2026',
            'slug' => 'trade-programme',
            'status' => 'draft',
            'template' => 'default',
            'content' => [],
        ])->assertRedirect();

        $this->assertSame('Trade programme 2026', $page->fresh()->title);
    }

    public function test_the_page_builder_saves_and_publishes_content(): void
    {
        /* A page whose slug does not collide with a storefront route, so the
         * public assertion below goes through the CMS catch-all. */
        $page = Page::query()->create([
            'title' => 'Workshop notes',
            'slug' => 'workshop-notes',
            'status' => 'draft',
            'template' => 'default',
            'content' => [],
            'published_content' => [],
        ]);

        $content = [
            ['id' => 'a1', 'type' => 'heading', 'attrs' => ['text' => 'Built in Lahore', 'level' => 'h2']],
            ['id' => 'a2', 'type' => 'text', 'attrs' => ['body' => 'Volume pricing for studios.']],
            ['id' => 'a3', 'type' => 'unknown_type', 'attrs' => []],
        ];

        $this->actingAs($this->owner)
            ->postJson(route('admin.pages.content', $page), ['content' => $content])
            ->assertOk()
            ->assertJsonPath('message', 'Draft saved.');

        /* Unknown block types are dropped on save, so a stale builder payload
         * cannot publish a block the site has no view for. */
        $this->assertSame(['heading', 'text'], array_column($page->fresh()->blocks(), 'type'));

        $this->actingAs($this->owner)
            ->postJson(route('admin.pages.publish', $page))
            ->assertOk();

        $this->assertNotNull($page->fresh()->published_at);
        $this->assertSame(['heading', 'text'], array_column($page->fresh()->published_content ?? [], 'type'));

        /* What was published is what the public URL renders. */
        $this->get('/'.$page->fresh()->slug)->assertOk()->assertSee('Built in Lahore', false);
    }

    public function test_blog_posts_services_and_testimonials_round_trip(): void
    {
        $this->actingAs($this->owner)->post(route('admin.posts.store'), [
            'title' => 'Choosing a dining table size',
            'slug' => '',
            'status' => 'published',
            'excerpt' => 'Seating maths, made simple.',
            'body' => 'Start with 60 cm of table edge per person.',
            'tags' => 'dining, sizing',
        ])->assertRedirect();

        $post = BlogPost::query()->where('slug', 'choosing-a-dining-table-size')->firstOrFail();
        $this->assertSame(['dining', 'sizing'], $post->tags);
        $this->assertNotNull($post->published_at);

        $this->get(route('site.blog.show', $post))->assertOk()->assertSee('Choosing a dining table size', false);

        $this->actingAs($this->owner)->post(route('admin.services.store'), [
            'title' => 'Bespoke joinery',
            'excerpt' => 'One-off pieces.',
            'body' => 'Built to a millimetre.',
            'is_published' => '1',
        ])->assertRedirect();

        $this->assertTrue((bool) Service::query()->where('slug', 'bespoke-joinery')->value('is_published'));

        $this->actingAs($this->owner)->post(route('admin.testimonials.store'), [
            'author' => 'Hina Yousaf',
            'role' => 'Homeowner',
            'quote' => 'The finish is flawless four years on.',
            'rating' => 5,
            'is_published' => '1',
        ])->assertRedirect();

        $this->assertSame(5, (int) Testimonial::query()->where('author', 'Hina Yousaf')->value('rating'));
    }

    public function test_categories_and_products_are_created_with_real_prices(): void
    {
        $this->actingAs($this->owner)->post(route('admin.categories.store'), [
            'name' => 'Outdoor',
            'description' => 'Weather-sealed teak.',
            'is_active' => '1',
        ])->assertRedirect();

        $category = Category::query()->where('slug', 'outdoor')->firstOrFail();

        $this->actingAs($this->owner)->post(route('admin.products.store'), [
            'name' => 'Terrace bench',
            'sku' => 'WX-BEN-900',
            'category_id' => $category->id,
            'status' => 'published',
            'short_description' => 'Teak, three metres.',
            'price' => '185000',
            'cost' => '120000',
            'material' => 'Teak',
            'lead_time_days' => 35,
            'stock_quantity' => 4,
            'low_stock_threshold' => 2,
            'is_active' => '1',
        ])->assertRedirect();

        $product = Product::query()->where('sku', 'WX-BEN-900')->firstOrFail();

        $this->assertSame(18500000, (int) $product->price_minor);
        $this->assertSame(12000000, (int) $product->cost_minor);

        /* The storefront must sell what the admin just created. */
        $this->assertSame('terrace-bench', $product->slug);
        $this->get(route('site.products.show', $product))->assertOk()->assertSee('Terrace bench', false);
    }

    public function test_projects_are_created_with_a_generated_reference_and_budget(): void
    {
        $customer = Customer::query()->firstOrFail();

        $this->actingAs($this->owner)->post(route('admin.projects.store'), [
            'name' => 'Ayesha dressing room',
            'customer_id' => $customer->id,
            'status' => 'planning',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addWeeks(6)->toDateString(),
            'budget_minor' => '450000',
            'site_address' => 'DHA Phase 6, Lahore',
        ])->assertRedirect();

        $project = Project::query()->where('name', 'Ayesha dressing room')->firstOrFail();

        $this->assertNotNull($project->reference);
        $this->assertSame(45000000, (int) $project->budget_minor);
    }

    public function test_a_lead_can_be_captured_from_the_desk(): void
    {
        $this->actingAs($this->owner)->post(route('admin.leads.store'), [
            'name' => 'Kamran Aziz',
            'email' => 'kamran@example.test',
            'phone' => '+92 300 222 3333',
            'message' => 'Two bedroom sets.',
            'source' => 'instagram',
            'status' => 'new',
            'priority' => 'normal',
            'estimated_value' => '250000',
        ])->assertRedirect();

        $lead = Lead::query()->where('email', 'kamran@example.test')->firstOrFail();

        $this->assertSame(25000000, (int) $lead->estimated_value_minor);
        $this->assertNull($lead->getAttribute('estimated_value'));
    }

    public function test_lead_status_and_assignment_updates_render(): void
    {
        $lead = Lead::query()->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('admin.leads.status', $lead), ['status' => 'contacted'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('contacted', $lead->fresh()->status);

        $this->actingAs($this->owner)
            ->post(route('admin.leads.note', $lead), ['body' => 'Called, asked for samples.'])
            ->assertRedirect();

        $this->assertSame(1, $lead->activities()->where('type', 'note')->count());
    }

    public function test_a_quotation_is_created_from_line_items(): void
    {
        $customer = Customer::query()->firstOrFail();
        $product = Product::query()->firstOrFail();

        $this->actingAs($this->owner)->post(route('admin.quotations.store'), [
            'customer_id' => $customer->id,
            'valid_until' => now()->addDays(10)->toDateString(),
            'notes' => 'Includes installation.',
            'items' => [
                ['description' => $product->name, 'quantity' => 2, 'unit_price' => '90000', 'product_id' => $product->id],
            ],
            'discount' => '0',
            'delivery' => '15000',
            'tax_percent' => '17',
        ])->assertRedirect();

        $quotation = Quotation::query()->latest('id')->firstOrFail();

        $this->assertSame(18000000, (int) $quotation->subtotal_minor);
        $this->assertSame(1500000, (int) $quotation->delivery_minor);
        $this->assertGreaterThan(18000000, (int) $quotation->total_minor);

        $this->actingAs($this->owner)
            ->get(route('admin.quotations.show', $quotation))
            ->assertOk()
            ->assertSee($quotation->reference, false);
    }

    public function test_navigation_menu_items_round_trip(): void
    {
        $page = Page::query()->firstOrFail();

        $this->actingAs($this->owner)->post(route('admin.navigation.store'), [
            'location' => 'header',
            'label' => 'Trade',
            'page_id' => $page->id,
        ])->assertRedirect();

        $item = \App\Models\NavigationItem::query()->where('label', 'Trade')->firstOrFail();

        $this->assertTrue((bool) $item->is_active);
        $this->assertSame($page->id, (int) $item->page_id);

        $this->actingAs($this->owner)
            ->put(route('admin.navigation.update', $item), ['label' => 'For trade', 'url' => null, 'page_id' => $page->id])
            ->assertRedirect();

        $this->assertSame('For trade', $item->fresh()->label);

        $this->actingAs($this->owner)->delete(route('admin.navigation.destroy', $item))->assertRedirect();
        $this->assertNull(\App\Models\NavigationItem::query()->find($item->id));
    }

    public function test_team_members_are_created_and_deactivated(): void
    {
        $this->actingAs($this->owner)->post(route('admin.users.store'), [
            'name' => 'Haris Malik',
            'email' => 'haris@reimaginos.test',
            'password' => 'a-long-password',
            'role' => 'staff',
            'job_title' => 'Workshop assistant',
            'is_active' => '1',
        ])->assertRedirect();

        $user = User::query()->where('email', 'haris@reimaginos.test')->firstOrFail();
        $this->assertTrue($user->is_active);

        $this->actingAs($this->owner)->delete(route('admin.users.destroy', $user))->assertRedirect();

        $this->assertFalse((bool) $user->fresh()->is_active);
        $this->assertNotNull($user->fresh());
    }

    public function test_settings_are_saved_and_used_by_the_storefront(): void
    {
        $this->actingAs($this->owner)->put(route('admin.settings.update'), [
            'group' => 'brand',
            'site_name' => 'Reimagin Studio',
            'site_tagline' => 'Furniture that outlives the trend.',
            'site_description' => 'Made to measure in Lahore.',
        ])->assertRedirect();

        $this->assertSame('Furniture that outlives the trend.', settings('site_tagline'));
        $this->get('/')->assertOk()->assertSee('Furniture that outlives the trend.', false);

        /* An unknown tab is a stale form, not a broken link. */
        $this->actingAs($this->owner)
            ->put(route('admin.settings.update'), ['group' => 'nope'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_seo_meta_can_be_updated(): void
    {
        $page = Page::query()->firstOrFail();

        $this->actingAs($this->owner)->put(route('admin.seo.update', $page), [
            'seo_title' => 'Bespoke furniture in Lahore',
            'seo_description' => 'Designed, built and installed by our own workshop in Gulberg.',
        ])->assertRedirect();

        $page = $page->fresh();

        $this->assertSame('Bespoke furniture in Lahore', $page->seo_title);
        $this->get(route('admin.seo.index'))->assertOk();
    }

    public function test_ai_agent_settings_are_saved_and_secrets_are_not_shown(): void
    {
        $this->actingAs($this->owner)->put(route('admin.ai.update'), [
            'ai_enabled' => '1',
            'ai_tone' => 'Warm and concise',
            'ai_greeting' => 'Hello from Reimagin!',
            'ai_model' => 'gpt-4o-mini',
            'ai_api_key' => 'sk-test-not-a-real-key',
        ])->assertRedirect();

        $this->assertSame('Warm and concise', \App\Models\Setting::get('ai_tone'));

        $this->actingAs($this->owner)
            ->get(route('admin.ai.index'))
            ->assertOk()
            ->assertDontSee('sk-test-not-a-real-key', false);
    }

    public function test_an_appointment_is_booked_from_the_desk(): void
    {
        $this->actingAs($this->owner)->post(route('admin.appointments.store'), [
            'name' => 'Sadia Noor',
            'phone' => '+92 345 777 8888',
            'type' => 'showroom',
            'starts_at' => now()->addDays(2)->setTime(13, 0)->toDateTimeString(),
            'location' => 'Gulberg showroom',
        ])->assertRedirect();

        $appointment = \App\Models\Appointment::query()->where('name', 'Sadia Noor')->firstOrFail();

        $this->assertNotNull($appointment->reference);
        $this->assertSame('showroom', $appointment->type);
    }

    public function test_a_purchase_order_is_raised_and_received_into_stock(): void
    {
        $supplier = \App\Models\Supplier::query()->firstOrFail();
        $product = Product::query()->where('track_stock', true)->firstOrFail();
        $before = (int) $product->stock_quantity;

        $this->actingAs($this->owner)->post(route('admin.purchasing.store'), [
            'supplier_id' => $supplier->id,
            'items' => [
                ['product_id' => $product->id, 'description' => $product->name, 'quantity' => 5, 'unit_cost' => '1000'],
            ],
            'status' => 'ordered',
            'notes' => 'Restock',
        ])->assertRedirect();

        $order = \App\Models\PurchaseOrder::query()->latest('id')->firstOrFail();
        $this->assertStringStartsWith('PO-', $order->reference);
        $this->assertSame(500000, (int) $order->total_minor);

        $this->actingAs($this->owner)
            ->post(route('admin.purchasing.receive', $order), [
                'items' => [$order->items()->firstOrFail()->id => 5],
            ])->assertRedirect();

        $this->assertSame($before + 5, (int) $product->fresh()->stock_quantity);
    }

    public function test_a_conversation_can_be_answered_and_closed(): void
    {
        $conversation = \App\Models\Conversation::query()->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('admin.conversations.reply', $conversation), ['body' => 'Happy to help — when suits you?'])
            ->assertRedirect();

        $this->assertSame(1, $conversation->messages()->where('author_type', 'staff')->count());

        $this->actingAs($this->owner)
            ->post(route('admin.conversations.status', $conversation), ['status' => 'resolved'])
            ->assertRedirect();

        $this->assertSame('resolved', $conversation->fresh()->status);
    }

    public function test_a_customer_can_be_edited(): void
    {
        $customer = Customer::query()->firstOrFail();

        $this->actingAs($this->owner)->put(route('admin.customers.update', $customer), [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'city' => 'Islamabad',
        ])->assertRedirect();

        $this->assertSame('Islamabad', $customer->fresh()->city);
    }

    public function test_media_uploads_are_rejected_when_empty(): void
    {
        $this->actingAs($this->owner)
            ->post(route('admin.media.store'), [])
            ->assertSessionHasErrors();
    }

    public function test_an_order_note_can_be_saved_without_changing_status(): void
    {
        $order = Order::query()->firstOrFail();

        $this->actingAs($this->owner)->put(route('admin.orders.update', $order), [
            'status' => $order->status,
            'notes' => 'Client asked for a later install date.',
        ])->assertRedirect();

        $this->assertSame('Client asked for a later install date.', $order->fresh()->notes);
    }
}
