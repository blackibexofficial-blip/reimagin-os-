<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\User;

/**
 * Single source of truth for the admin panel's sidebar.
 *
 * Each item declares the minimum role it is readable by, so the layout never
 * needs to hard-code permission logic and a nav entry can never point at a
 * route that does not exist (the builder filters unknown routes out).
 */
class Navigation
{
    /** @return array<int, array{label: string, items: array<int, array<string, mixed>>}> */
    public static function for(User $user): array
    {
        $sections = [];

        foreach (self::definition() as $section) {
            $items = array_values(array_filter(
                $section['items'],
                fn (array $item) => $user->hasRoleAtLeast($item['role']) && route_exists($item['route'])
            ));

            if ($items !== []) {
                $sections[] = ['label' => $section['label'], 'items' => $items];
            }
        }

        return $sections;
    }

    /** @return array<int, array{label: string, items: array<int, array<string, mixed>>}> */
    public static function definition(): array
    {
        return [
            [
                'label' => 'Overview',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'grid', 'role' => Role::Staff, 'match' => 'admin.dashboard'],
                    ['label' => 'Live storefront', 'route' => 'site.home', 'icon' => 'external', 'role' => Role::Staff, 'match' => null, 'external' => true],
                ],
            ],
            [
                'label' => 'Website',
                'items' => [
                    ['label' => 'Pages', 'route' => 'admin.pages.index', 'icon' => 'file', 'role' => Role::Staff, 'match' => 'admin.pages.*'],
                    ['label' => 'Blog', 'route' => 'admin.posts.index', 'icon' => 'note', 'role' => Role::Staff, 'match' => 'admin.posts.*'],
                    ['label' => 'Services', 'route' => 'admin.services.index', 'icon' => 'wrench', 'role' => Role::Staff, 'match' => 'admin.services.*'],
                    ['label' => 'Projects', 'route' => 'admin.projects.index', 'icon' => 'layers', 'role' => Role::Staff, 'match' => 'admin.projects.*'],
                    ['label' => 'Testimonials', 'route' => 'admin.testimonials.index', 'icon' => 'star', 'role' => Role::Staff, 'match' => 'admin.testimonials.*'],
                    ['label' => 'Navigation', 'route' => 'admin.navigation.index', 'icon' => 'menu', 'role' => Role::Manager, 'match' => 'admin.navigation.*'],
                    ['label' => 'Media library', 'route' => 'admin.media.index', 'icon' => 'image', 'role' => Role::Staff, 'match' => 'admin.media.*'],
                ],
            ],
            [
                'label' => 'Commerce',
                'items' => [
                    ['label' => 'Products', 'route' => 'admin.products.index', 'icon' => 'box', 'role' => Role::Staff, 'match' => 'admin.products.*'],
                    ['label' => 'Categories', 'route' => 'admin.categories.index', 'icon' => 'tag', 'role' => Role::Staff, 'match' => 'admin.categories.*'],
                    ['label' => 'Orders', 'route' => 'admin.orders.index', 'icon' => 'cart', 'role' => Role::Staff, 'match' => 'admin.orders.*'],
                    ['label' => 'Quotations', 'route' => 'admin.quotations.index', 'icon' => 'document', 'role' => Role::Staff, 'match' => 'admin.quotations.*'],
                    ['label' => 'Appointments', 'route' => 'admin.appointments.index', 'icon' => 'calendar', 'role' => Role::Staff, 'match' => 'admin.appointments.*'],
                ],
            ],
            [
                'label' => 'Customers',
                'items' => [
                    ['label' => 'Leads', 'route' => 'admin.leads.index', 'icon' => 'spark', 'role' => Role::Staff, 'match' => 'admin.leads.*'],
                    ['label' => 'Customers', 'route' => 'admin.customers.index', 'icon' => 'users', 'role' => Role::Staff, 'match' => 'admin.customers.*'],
                    ['label' => 'Conversations', 'route' => 'admin.conversations.index', 'icon' => 'chat', 'role' => Role::Staff, 'match' => 'admin.conversations.*'],
                    ['label' => 'AI sales agent', 'route' => 'admin.ai.index', 'icon' => 'spark', 'role' => Role::Manager, 'match' => 'admin.ai.*'],
                ],
            ],
            [
                'label' => 'Operations',
                'items' => [
                    ['label' => 'Inventory', 'route' => 'admin.inventory.index', 'icon' => 'layers', 'role' => Role::Manager, 'match' => 'admin.inventory.*'],
                    ['label' => 'Purchasing', 'route' => 'admin.purchasing.index', 'icon' => 'truck', 'role' => Role::Manager, 'match' => 'admin.purchasing.*'],
                    ['label' => 'Production', 'route' => 'admin.production.index', 'icon' => 'wrench', 'role' => Role::Manager, 'match' => 'admin.production.*'],
                    ['label' => 'Finance', 'route' => 'admin.finance.index', 'icon' => 'banknote', 'role' => Role::Manager, 'match' => 'admin.finance.*'],
                ],
            ],
            [
                'label' => 'Growth',
                'items' => [
                    ['label' => 'SEO', 'route' => 'admin.seo.index', 'icon' => 'globe', 'role' => Role::Manager, 'match' => 'admin.seo.*'],
                    ['label' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'chart', 'role' => Role::Manager, 'match' => 'admin.reports.*'],
                ],
            ],
            [
                'label' => 'System',
                'items' => [
                    ['label' => 'Team', 'route' => 'admin.users.index', 'icon' => 'users', 'role' => Role::Admin, 'match' => 'admin.users.*'],
                    ['label' => 'Settings', 'route' => 'admin.settings.index', 'icon' => 'cog', 'role' => Role::Admin, 'match' => 'admin.settings.*'],
                    ['label' => 'Security', 'route' => 'admin.security.index', 'icon' => 'shield', 'role' => Role::Admin, 'match' => 'admin.security.*'],
                ],
            ],
        ];
    }
}
