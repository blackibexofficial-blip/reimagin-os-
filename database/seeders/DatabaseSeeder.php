<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Appointment;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\NavigationItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\Setting;
use App\Models\ShowcaseProject;
use App\Models\Supplier;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\Blocks\BlockRegistry;
use App\Support\Slug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a complete, internally consistent studio dataset.
 *
 * Hand-written rather than factory-driven: fakerphp/faker is not vendored in
 * this install, and the copy should read like the studio's own material. Totals
 * are recalculated through the same services the admin uses, so seeded figures
 * match what the UI would produce.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = $this->users();
        $this->settings();
        $pages = $this->pages($owner);
        $categories = $this->categories();
        $products = $this->products($categories);
        $customers = $this->customers();
        $this->leads($products);
        $orders = $this->orders($customers, $products);
        $this->quotations($customers, $products);
        $this->projects($customers, $orders);
        $this->content();
        $this->navigation($pages);
        $this->suppliers();
        $this->appointments($owner);
        $this->conversations($customers);
    }

    private function users(): User
    {
        $people = [
            ['name' => 'Studio Owner', 'email' => 'owner@reimaginos.test', 'role' => Role::Owner, 'job_title' => 'Founder'],
            ['name' => 'Ayesha Khan', 'email' => 'admin@reimaginos.test', 'role' => Role::Admin, 'job_title' => 'Operations lead'],
            ['name' => 'Bilal Ahmed', 'email' => 'manager@reimaginos.test', 'role' => Role::Manager, 'job_title' => 'Production manager'],
            ['name' => 'Sana Iqbal', 'email' => 'sales@reimaginos.test', 'role' => Role::Staff, 'job_title' => 'Sales consultant'],
        ];

        $owner = null;

        foreach ($people as $person) {
            $user = User::query()->updateOrCreate(
                ['email' => $person['email']],
                $person + ['password' => Hash::make('password'), 'is_active' => true],
            );

            $owner ??= $user;
        }

        return $owner;
    }

    private function settings(): void
    {
        $values = [
            'company_name' => 'Reimagin Studio',
            'tagline' => 'Bespoke furniture designed, built and installed by our own workshop.',
            'phone' => '+92 42 111 222 333',
            'whatsapp' => '+923001234567',
            'email' => 'hello@reimaginos.test',
            'address' => '12-C Gulberg III, Lahore',
            'city' => 'Lahore',
            'currency' => 'PKR',
        ];

        foreach ($values as $key => $value) {
            Setting::put($key, $value, 'company');
        }
    }

    /** @return array<string, Page> */
    private function pages(User $owner): array
    {
        $definitions = [
            'home' => [
                'title' => 'Reimagin Studio',
                'is_homepage' => true,
                'order' => 1,
                'seo_title' => 'Reimagin Studio — bespoke furniture in Lahore',
                'seo_description' => 'Custom furniture designed and built in our Lahore workshop. Wardrobes, kitchens and fitted joinery, installed by our own team.',
                'blocks' => [
                    ['type' => 'hero', 'attrs' => [
                        'eyebrow' => 'Lahore · Est. 2014',
                        'heading' => 'Furniture made for the way you actually live',
                        'body' => 'We design, build and install fitted wardrobes, kitchens and statement pieces from our own workshop — one team from sketch to handover.',
                        'cta_label' => 'Request a quotation',
                        'cta_url' => '/contact',
                        'secondary_label' => 'See our projects',
                        'secondary_url' => '/projects',
                    ]],
                    ['type' => 'product_grid', 'attrs' => ['heading' => 'Featured pieces', 'limit' => 6, 'only_featured' => true]],
                    ['type' => 'services_list', 'attrs' => ['heading' => 'What we do', 'limit' => 4]],
                    ['type' => 'testimonials', 'attrs' => ['heading' => 'What clients say', 'limit' => 3]],
                    ['type' => 'quote_form', 'attrs' => [
                        'heading' => 'Tell us about your space',
                        'body' => 'Send the room, the dimensions and the look you have in mind. We reply within one working day.',
                    ]],
                ],
            ],
            'projects' => [
                'title' => 'Projects',
                'order' => 2,
                'blocks' => [
                    ['type' => 'heading', 'attrs' => ['text' => 'Selected work', 'level' => 'h1']],
                    ['type' => 'project_gallery', 'attrs' => ['heading' => 'Recent installations', 'limit' => 6]],
                ],
            ],
            'services' => [
                'title' => 'Services',
                'order' => 3,
                'blocks' => [
                    ['type' => 'heading', 'attrs' => ['text' => 'How we work', 'level' => 'h1']],
                    ['type' => 'services_list', 'attrs' => ['heading' => 'Services', 'limit' => 6]],
                ],
            ],
            'contact' => [
                'title' => 'Contact',
                'order' => 4,
                'seo_title' => 'Contact Reimagin Studio',
                'seo_description' => 'Visit the showroom in Gulberg III, Lahore, or send us your project details.',
                'blocks' => [
                    ['type' => 'heading', 'attrs' => ['text' => 'Talk to the studio', 'level' => 'h1']],
                    ['type' => 'text', 'attrs' => ['body' => 'Showroom: 12-C Gulberg III, Lahore. Open Monday to Saturday, 11am–8pm.']],
                    ['type' => 'quote_form', 'attrs' => ['heading' => 'Send a project brief']],
                    ['type' => 'appointment_form', 'attrs' => ['heading' => 'Book a showroom visit']],
                ],
            ],
        ];

        $pages = [];

        foreach ($definitions as $slug => $definition) {
            $page = Page::query()->updateOrCreate(['slug' => $slug], [
                'title' => $definition['title'],
                'status' => 'published',
                'is_homepage' => (bool) ($definition['is_homepage'] ?? false),
                'show_in_navigation' => true,
                'navigation_order' => $definition['order'],
                'author_id' => $owner->id,
                'seo_title' => $definition['seo_title'] ?? null,
                'seo_description' => $definition['seo_description'] ?? null,
                'published_at' => now()->subDays(20),
            ]);

            $blocks = BlockRegistry::normalize($definition['blocks']);
            $page->forceFill(['content' => $blocks, 'published_content' => $blocks])->save();

            $pages[$slug] = $page;
        }

        /* A draft page proves the builder's draft/publish split without going live. */
        $draft = Page::query()->updateOrCreate(['slug' => 'kitchens'], [
            'title' => 'Kitchens',
            'status' => 'draft',
            'author_id' => $owner->id,
            'seo_title' => 'Bespoke kitchens',
        ]);

        $draft->forceFill(['content' => BlockRegistry::normalize([
            ['type' => 'heading', 'attrs' => ['text' => 'Kitchens built around your cooking', 'level' => 'h1']],
            ['type' => 'text', 'attrs' => ['body' => 'Draft copy — not visible on the live site until this page is published.']],
        ])])->save();

        $pages['kitchens'] = $draft;

        return $pages;
    }

    /** @return array<string, Category> */
    private function categories(): array
    {
        $rows = [
            'wardrobes' => ['Wardrobes', 'Fitted and freestanding storage built to the millimetre.'],
            'kitchens' => ['Kitchens', 'Modular kitchens with solid carcasses and soft-close hardware.'],
            'living' => ['Living & dining', 'Tables, consoles and media walls in solid hardwood.'],
            'office' => ['Workspace', 'Desks, credenzas and meeting tables for home offices.'],
        ];

        $out = [];

        foreach ($rows as $slug => [$name, $description]) {
            $out[$slug] = Category::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'is_active' => true,
            ]);
        }

        return $out;
    }

    /** @return array<string, Product> */
    private function products(array $categories): array
    {
        $rows = [
            ['gulberg-wardrobe', 'Gulberg Sliding Wardrobe', 'wardrobes', 'WRD-101', 285000, 178000, 'American walnut veneer', 'Matte lacquer', 5, 6, true],
            ['dha-walk-in', 'DHA Walk-in Closet System', 'wardrobes', 'WRD-204', 640000, 402000, 'Oak veneer + aluminium', 'Powder coat', 3, 2, true],
            ['cantt-modular-kitchen', 'Cantt Modular Kitchen', 'kitchens', 'KTN-310', 1250000, 812000, 'Marine ply carcass, quartz top', 'Satin', 2, 1, true],
            ['johar-island', 'Johar Kitchen Island', 'kitchens', 'KTN-118', 410000, 245000, 'Solid ash with stone top', 'Oil rubbed', 4, 3, false],
            ['model-town-dining', 'Model Town Dining Table', 'living', 'LIV-450', 320000, 196000, 'Solid sheesham', 'Natural oil', 3, 4, true],
            ['garden-town-console', 'Garden Town Console', 'living', 'LIV-220', 145000, 88000, 'Walnut veneer', 'Matte lacquer', 6, 8, false],
            ['study-desk', 'Executive Study Desk', 'office', 'OFC-115', 210000, 128000, 'Oak with leather inlay', 'Hand rubbed', 4, 5, false],
            ['boardroom-table', 'Boardroom Table 10-seat', 'office', 'OFC-900', 0, 480000, 'Solid walnut', 'Satin lacquer', 2, 0, false],
        ];

        $out = [];

        foreach ($rows as [$slug, $name, $category, $sku, $price, $cost, $material, $finish, $threshold, $stock, $featured]) {
            $product = Product::query()->updateOrCreate(['sku' => $sku], [
                'name' => $name,
                'slug' => $slug,
                'category_id' => $categories[$category]->id,
                'status' => 'published',
                'short_description' => $material.' — '.$finish,
                'description' => 'Built to order in our Lahore workshop. Includes site survey, delivery and installation by our own team.',
                'price_minor' => $price,
                'cost_minor' => $cost,
                'currency' => 'PKR',
                'is_quote_only' => $price === 0,
                'is_featured' => $featured,
                'is_active' => true,
                'track_stock' => true,
                'stock_quantity' => $stock,
                'low_stock_threshold' => $threshold,
                'material' => $material,
                'finish' => $finish,
                'lead_time_days' => 28,
                'dimensions' => '240 × 60 × 220 cm',
            ]);

            $out[$slug] = $product;
        }

        return $out;
    }

    /** @return array<string, Customer> */
    private function customers(): array
    {
        $rows = [
            ['Hamza Tariq', 'hamza.tariq@example.com', '+923004455661', 'Tariq Residence', 'DHA Phase 5, Lahore'],
            ['Fatima Sheikh', 'fatima.sheikh@example.com', '+923214455662', 'Sheikh Apartments', 'Gulberg II, Lahore'],
            ['Usman Raza', 'usman.raza@example.com', '+923334455663', 'Raza Villa', 'Bahria Town, Lahore'],
            ['Nadia Malik', 'nadia.malik@example.com', '+923454455664', null, 'Model Town, Lahore'],
        ];

        $out = [];

        foreach ($rows as [$name, $email, $phone, $company, $address]) {
            $out[$email] = Customer::query()->updateOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => $phone,
                'company' => $company,
                'address_line_1' => $address,
                'city' => 'Lahore',
                'country' => 'PK',
            ]);
        }

        return $out;
    }

    private function leads(array $products): void
    {
        $rows = [
            ['Imran Yousaf', 'imran.yousaf@example.com', '+923011122334', 'qualified', 'high', 450000, 'gulberg-wardrobe', 'website'],
            ['Zara Hassan', 'zara.hassan@example.com', '+923021122335', 'new', 'normal', 1200000, 'cantt-modular-kitchen', 'whatsapp'],
            ['Kashif Baig', 'kashif.baig@example.com', null, 'contacted', 'normal', 320000, 'model-town-dining', 'walk_in'],
            ['Sadia Noor', 'sadia.noor@example.com', '+923041122337', 'proposal', 'high', 2100000, 'cantt-modular-kitchen', 'ai_agent'],
        ];

        foreach ($rows as $index => [$name, $email, $phone, $status, $priority, $value, $product, $source]) {
            $lead = Lead::query()->firstOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => $phone,
                'source' => $source,
                'status' => $status,
                'priority' => $priority,
                'estimated_value_minor' => $value,
                'product_id' => $products[$product]->id,
                'message' => 'Looking for a fitted solution for a '.($index + 2).'-bedroom home.',
                'next_follow_up_at' => now()->addDays($index + 1),
            ]);

            $lead->forceFill(['score' => $lead->calculateScore()])->save();
        }
    }

    /** @return array<int, Order> */
    private function orders(array $customers, array $products): array
    {
        $rows = [
            ['hamza.tariq@example.com', [['gulberg-wardrobe', 1]], 'confirmed', 40, 30],
            ['fatima.sheikh@example.com', [['cantt-modular-kitchen', 1], ['johar-island', 1]], 'in_production', 100, 22],
            ['usman.raza@example.com', [['model-town-dining', 1], ['garden-town-console', 2]], 'delivered', 100, 14],
        ];

        $orders = [];
        $creator = User::query()->value('id');

        foreach ($rows as $index => [$email, $lines, $status, $paidPercent, $daysAgo]) {
            $reference = 'SO-'.now()->format('Y').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            $order = Order::query()->firstOrCreate(['reference' => $reference], [
                'customer_id' => $customers[$email]->id,
                'status' => $status,
                'currency' => 'PKR',
                'placed_at' => now()->subDays($daysAgo),
                'confirmed_at' => now()->subDays($daysAgo)->addDay(),
                'delivered_at' => $status === 'delivered' ? now()->subDays(2) : null,
                'created_by' => $creator,
            ]);

            if ($order->items()->doesntExist()) {
                $subtotal = 0;

                foreach ($lines as [$slug, $quantity]) {
                    $product = $products[$slug];
                    $lineTotal = (int) $product->price_minor * $quantity;
                    $subtotal += $lineTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'quantity' => $quantity,
                        'unit_price_minor' => (int) $product->price_minor,
                        'line_total_minor' => $lineTotal,
                    ]);
                }

                $order->forceFill(['subtotal_minor' => $subtotal, 'total_minor' => $subtotal])->save();

                $paid = (int) round($subtotal * $paidPercent / 100);

                if ($paid > 0) {
                    Payment::create([
                        'order_id' => $order->id,
                        'reference' => 'PM-'.$reference.'-01',
                        'method' => 'bank_transfer',
                        'type' => $paidPercent === 100 ? 'full' : 'advance',
                        'status' => 'confirmed',
                        'amount_minor' => $paid,
                        'currency' => 'PKR',
                        'paid_at' => now()->subDays(max(1, $daysAgo - 3)),
                        'recorded_by' => $creator,
                    ]);
                }
            }

            $order->recalculatePaymentState();
            $orders[] = $order->refresh();
        }

        foreach ($customers as $customer) {
            $customer->recalculateLifetimeValue();
        }

        return $orders;
    }

    private function quotations(array $customers, array $products): void
    {
        $rows = [
            ['hamza.tariq@example.com', 'draft', [['dha-walk-in', 1]]],
            ['nadia.malik@example.com', 'sent', [['cantt-modular-kitchen', 1]]],
        ];

        foreach ($rows as $index => [$email, $status, $lines]) {
            $reference = 'QT-'.now()->format('Y').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            $quotation = Quotation::query()->firstOrCreate(['reference' => $reference], [
                'customer_id' => $customers[$email]->id,
                'status' => $status,
                'currency' => 'PKR',
                'valid_until' => now()->addDays(14),
                'created_by' => User::query()->value('id'),
                'sent_at' => $status === 'sent' ? now()->subDay() : null,
            ]);

            if ($quotation->items()->doesntExist()) {
                $position = 0;

                foreach ($lines as [$slug, $quantity]) {
                    $product = $products[$slug];

                    $quotation->items()->create([
                        'product_id' => $product->id,
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price_minor' => (int) $product->price_minor,
                        'line_total_minor' => (int) $product->price_minor * $quantity,
                        'position' => $position++,
                    ]);
                }

                $quotation->recalculateTotals();
            }
        }
    }

    private function projects(array $customers, array $orders): void
    {
        $order = $orders[1] ?? null;

        if (! $order) {
            return;
        }

        $project = Project::query()->firstOrCreate(['reference' => 'PRJ-'.now()->format('Y').'-0001'], [
            'name' => 'Sheikh kitchen installation',
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'status' => 'production',
            'start_date' => now()->subDays(12),
            'due_date' => now()->addDays(18),
            'budget_minor' => (int) $order->total_minor,
            'site_address' => 'Sheikh Apartments, Gulberg II, Lahore',
        ]);

        if ($project->stages()->doesntExist()) {
            $stages = ['Design sign-off', 'Material procurement', 'Carcass build', 'Finishing', 'Delivery & install'];

            foreach ($stages as $position => $name) {
                ProjectStage::create([
                    'project_id' => $project->id,
                    'name' => $name,
                    'status' => $position < 2 ? 'done' : ($position === 2 ? 'in_progress' : 'pending'),
                    'position' => $position + 1,
                    'started_on' => now()->subDays(12 - $position),
                    'completed_on' => $position < 2 ? now()->subDays(10 - $position) : null,
                ]);
            }
        }
    }

    private function content(): void
    {
        $services = [
            ['Bespoke design', 'Concept boards, 3D visuals and shop drawings before a single cut is made.'],
            ['Custom manufacturing', 'Built in our own workshop with solid carcasses and branded hardware.'],
            ['Site installation', 'Our fitters handle delivery, levelling, scribing and the final handover.'],
            ['Renovation', 'Joinery upgrades that work with your existing structure and services.'],
        ];

        foreach ($services as $position => [$title, $excerpt]) {
            Service::query()->updateOrCreate(['slug' => Slug::unique($title, 'services')], [
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $excerpt.' We survey the space, agree the specification in writing and keep you updated at each production stage.',
                'is_published' => true,
                'position' => $position + 1,
            ]);
        }

        $portfolio = [
            ['DHA Phase 6 residence', 'DHA Phase 6', 2024, 'Full joinery package across four bedrooms and a study.'],
            ['Gulberg penthouse', 'Gulberg III', 2023, 'Walnut wardrobe wall with concealed lighting.'],
            ['Model Town kitchen', 'Model Town', 2024, 'L-shaped kitchen with quartz worktops and a breakfast island.'],
        ];

        foreach ($portfolio as $position => [$title, $location, $year, $summary]) {
            ShowcaseProject::query()->updateOrCreate(['slug' => Slug::unique($title, 'showcase_projects')], [
                'title' => $title,
                'location' => $location,
                'year' => $year,
                'summary' => $summary,
                'description' => $summary.' Photography and drawings available on request.',
                'category' => 'Residential',
                'is_published' => true,
                'is_featured' => $position === 0,
                'position' => $position + 1,
            ]);
        }

        $testimonials = [
            ['Hamza Tariq', 'Homeowner', 'They handled everything from the first sketch to the final screw. Nothing was outsourced.'],
            ['Fatima Sheikh', 'Homeowner', 'The kitchen was installed on the agreed date and the finish is flawless.'],
            ['Usman Raza', 'Architect', 'Reliable drawings and honest timelines — rare with joinery contractors.'],
        ];

        foreach ($testimonials as $position => [$author, $role, $quote]) {
            Testimonial::query()->updateOrCreate(['author' => $author], [
                'role' => $role,
                'quote' => $quote,
                'rating' => 5,
                'is_published' => true,
                'position' => $position + 1,
            ]);
        }

        $journal = BlogCategory::query()->firstOrCreate(
            ['slug' => 'craft'],
            ['name' => 'Craft notes', 'description' => 'How our pieces are designed and built.'],
        );

        $posts = [
            ['Choosing a wardrobe carcass material', 'Marine ply resists Lahore humidity far better than MDF. Here is what we specify and why.'],
            ['Kitchen layouts that age well', 'The work triangle still matters — but so do drawers. Our checklist before you sign off.'],
        ];

        foreach ($posts as $index => [$title, $excerpt]) {
            BlogPost::query()->updateOrCreate(['slug' => Slug::unique($title, 'blog_posts')], [
                'title' => $title,
                'category_id' => $journal->id,
                'excerpt' => $excerpt,
                'body' => $excerpt."\n\nWe are happy to walk you through samples at the showroom before you commit.",
                'status' => 'published',
                'tags' => ['materials', 'process'],
                'published_at' => now()->subDays(10 - $index),
                'author_id' => User::query()->value('id'),
            ]);
        }
    }

    private function navigation(array $pages): void
    {
        $header = [
            ['Home', $pages['home']->id, 1],
            ['Projects', $pages['projects']->id, 2],
            ['Services', $pages['services']->id, 3],
            ['Contact', $pages['contact']->id, 4],
        ];

        foreach ($header as [$label, $pageId, $position]) {
            NavigationItem::query()->updateOrCreate(
                ['location' => 'header', 'label' => $label],
                ['page_id' => $pageId, 'position' => $position, 'is_active' => true],
            );
        }

        foreach (['Projects' => $pages['projects'], 'Services' => $pages['services'], 'Contact' => $pages['contact']] as $label => $page) {
            NavigationItem::query()->updateOrCreate(
                ['location' => 'footer', 'label' => $label],
                ['page_id' => $page->id, 'is_active' => true],
            );
        }
    }

    private function suppliers(): void
    {
        Supplier::query()->updateOrCreate(['name' => 'Punjab Timber Traders'], [
            'contact_name' => 'Rashid Ali',
            'phone' => '+924237778899',
            'city' => 'Lahore',
            'lead_time_days' => 7,
            'is_active' => true,
        ]);

        Supplier::query()->updateOrCreate(['name' => 'Hettich Hardware Pakistan'], [
            'contact_name' => 'Farah Naz',
            'email' => 'orders@example.com',
            'city' => 'Karachi',
            'lead_time_days' => 14,
            'is_active' => true,
        ]);
    }

    private function appointments(User $owner): void
    {
        if (Appointment::query()->exists()) {
            return;
        }

        $slots = [
            ['Ahmed Bilal', '+923001234567', 'showroom_visit', now()->setTime(12, 0), 'confirmed'],
            ['Mehwish Ali', '+923009876543', 'site_survey', now()->addDay()->setTime(15, 0), 'requested'],
        ];

        foreach ($slots as [$name, $phone, $type, $start, $status]) {
            Appointment::create([
                'reference' => \App\Services\AppointmentService::nextReference(),
                'name' => $name,
                'phone' => $phone,
                'type' => $type,
                'starts_at' => $start,
                'ends_at' => $start->copy()->addMinutes(45),
                'staff_id' => $owner->id,
                'status' => $status,
                'location' => '12-C Gulberg III, Lahore',
            ]);
        }
    }

    private function conversations(array $customers): void
    {
        if (Conversation::query()->exists()) {
            return;
        }

        $conversation = Conversation::create([
            'channel' => 'web',
            'status' => 'open',
            'customer_id' => $customers['hamza.tariq@example.com']->id,
            'guest_name' => 'Hamza Tariq',
            'guest_phone' => '+923004455661',
            'subject' => 'Wardrobe quotation',
            'last_message_at' => now()->subMinutes(20),
            'unread_for_staff' => 1,
        ]);

        $conversation->messages()->createMany([
            ['author_type' => 'visitor', 'body' => 'Hi, how much would a sliding wardrobe cost for a 12 foot wall?'],
            ['author_type' => 'agent', 'body' => 'Most fitted wardrobes land between Rs 250,000 and Rs 650,000 depending on span. Send me the room dimensions and I will get an itemised quotation from the studio.'],
            ['author_type' => 'visitor', 'body' => 'It is 12 feet wide, floor to ceiling.'],
        ]);
    }
}
