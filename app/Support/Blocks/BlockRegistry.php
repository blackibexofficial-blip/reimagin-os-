<?php

namespace App\Support\Blocks;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

/**
 * The single registry of block types the visual builder can place.
 *
 * Adding a widget means adding one entry here plus one Blade view in
 * resources/views/site/blocks/{type}.blade.php — nothing else needs to change,
 * because the builder UI, the inspector and the storefront renderer all read
 * from this registry.
 */
final class BlockRegistry
{
    public const GROUPS = ['Structure', 'Content', 'Shop', 'Studio', 'Advanced'];

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            /* ---------------------------------------------------------- Structure */
            'section' => [
                'label' => 'Section',
                'group' => 'Structure',
                'icon' => 'layers',
                'description' => 'A padded wrapper that holds other blocks.',
                'container' => true,
                'attrs' => [
                    'background' => ['type' => 'select', 'label' => 'Background', 'default' => 'white', 'options' => ['white' => 'White', 'muted' => 'Warm grey', 'dark' => 'Ink', 'brand' => 'Brand']],
                    'padding' => ['type' => 'select', 'label' => 'Padding', 'default' => 'md', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'none' => 'None']],
                    'max_width' => ['type' => 'select', 'label' => 'Width', 'default' => 'container', 'options' => ['container' => 'Contained', 'wide' => 'Wide', 'full' => 'Full width']],
                ],
            ],
            'columns' => [
                'label' => 'Columns',
                'group' => 'Structure',
                'icon' => 'grid',
                'description' => 'Split content into 2–4 columns.',
                'container' => true,
                'attrs' => [
                    'count' => ['type' => 'select', 'label' => 'Columns', 'default' => '2', 'options' => ['2' => 'Two', '3' => 'Three', '4' => 'Four']],
                    'gap' => ['type' => 'select', 'label' => 'Gap', 'default' => 'md', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large']],
                ],
            ],
            'spacer' => [
                'label' => 'Spacer',
                'group' => 'Structure',
                'icon' => 'arrow-path',
                'description' => 'Vertical breathing room.',
                'attrs' => [
                    'size' => ['type' => 'select', 'label' => 'Height', 'default' => 'md', 'options' => ['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra large']],
                ],
            ],

            /* ------------------------------------------------------------ Content */
            'heading' => [
                'label' => 'Heading',
                'group' => 'Content',
                'icon' => 'document',
                'description' => 'Section title with a level and alignment.',
                'attrs' => [
                    'text' => ['type' => 'text', 'label' => 'Text', 'default' => 'A headline that says something'],
                    'level' => ['type' => 'select', 'label' => 'Level', 'default' => 'h2', 'options' => ['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4']],
                    'align' => ['type' => 'align', 'label' => 'Alignment', 'default' => 'left'],
                ],
            ],
            'text' => [
                'label' => 'Text',
                'group' => 'Content',
                'icon' => 'note',
                'description' => 'A paragraph of body copy.',
                'attrs' => [
                    'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => 'Write the story of this piece — the material, the room it was made for, the detail that took the longest.'],
                    'size' => ['type' => 'select', 'label' => 'Size', 'default' => 'base', 'options' => ['sm' => 'Small', 'base' => 'Normal', 'lg' => 'Large']],
                    'align' => ['type' => 'align', 'label' => 'Alignment', 'default' => 'left'],
                ],
            ],
            'button' => [
                'label' => 'Button',
                'group' => 'Content',
                'icon' => 'arrow-path',
                'description' => 'A call to action linking anywhere.',
                'attrs' => [
                    'label' => ['type' => 'text', 'label' => 'Label', 'default' => 'Request a quotation'],
                    'url' => ['type' => 'url', 'label' => 'Link', 'default' => '/contact'],
                    'style' => ['type' => 'select', 'label' => 'Style', 'default' => 'primary', 'options' => ['primary' => 'Primary', 'secondary' => 'Outline', 'brand' => 'Brand']],
                    'align' => ['type' => 'align', 'label' => 'Alignment', 'default' => 'left'],
                ],
            ],
            'image' => [
                'label' => 'Image',
                'group' => 'Content',
                'icon' => 'image',
                'description' => 'A single image with optional caption.',
                'attrs' => [
                    'media_id' => ['type' => 'media', 'label' => 'Image', 'default' => null],
                    'caption' => ['type' => 'text', 'label' => 'Caption', 'default' => ''],
                    'ratio' => ['type' => 'select', 'label' => 'Aspect ratio', 'default' => '4/3', 'options' => ['4/3' => 'Landscape 4:3', '16/9' => 'Wide 16:9', '1/1' => 'Square', '3/4' => 'Portrait 3:4']],
                ],
            ],
            'hero' => [
                'label' => 'Hero',
                'group' => 'Content',
                'icon' => 'spark',
                'description' => 'Full-width opener with headline and two actions.',
                'attrs' => [
                    'eyebrow' => ['type' => 'text', 'label' => 'Eyebrow', 'default' => 'Lahore · Est. 2014'],
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Furniture made for the way you live'],
                    'body' => ['type' => 'textarea', 'label' => 'Body', 'default' => 'Designed, built and installed by one team — from the first sketch to the final screw.'],
                    'cta_label' => ['type' => 'text', 'label' => 'Primary label', 'default' => 'Request a quotation'],
                    'cta_url' => ['type' => 'url', 'label' => 'Primary link', 'default' => '/contact'],
                    'secondary_label' => ['type' => 'text', 'label' => 'Secondary label', 'default' => 'See our work'],
                    'secondary_url' => ['type' => 'url', 'label' => 'Secondary link', 'default' => '/projects'],
                    'media_id' => ['type' => 'media', 'label' => 'Background image', 'default' => null],
                    'align' => ['type' => 'align', 'label' => 'Alignment', 'default' => 'left'],
                ],
            ],
            'faq' => [
                'label' => 'FAQ',
                'group' => 'Content',
                'icon' => 'chat',
                'description' => 'Expandable questions and answers.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Questions we get asked'],
                    'items' => ['type' => 'repeater', 'label' => 'Questions', 'default' => [
                        ['question' => 'How long does a fitted wardrobe take?', 'answer' => 'Four to six weeks from signed drawings, including installation.'],
                        ['question' => 'Do you visit the site?', 'answer' => 'Yes — every project starts with a free survey and measurement visit.'],
                    ], 'fields' => ['question' => 'Question', 'answer' => 'Answer']],
                ],
            ],
            'html' => [
                'label' => 'Custom HTML',
                'group' => 'Advanced',
                'icon' => 'cog',
                'description' => 'Embed code, sanitised before it is rendered.',
                'attrs' => [
                    'html' => ['type' => 'code', 'label' => 'HTML', 'default' => ''],
                ],
            ],

            /* --------------------------------------------------------------- Shop */
            'product_grid' => [
                'label' => 'Product grid',
                'group' => 'Shop',
                'icon' => 'box',
                'description' => 'A grid of catalogue products.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Featured pieces'],
                    'limit' => ['type' => 'number', 'label' => 'How many', 'default' => 6, 'min' => 1, 'max' => 24],
                    'category_id' => ['type' => 'category', 'label' => 'Category', 'default' => null],
                    'only_featured' => ['type' => 'boolean', 'label' => 'Featured only', 'default' => false],
                    'columns' => ['type' => 'select', 'label' => 'Columns', 'default' => '3', 'options' => ['2' => 'Two', '3' => 'Three', '4' => 'Four']],
                ],
            ],
            'product_carousel' => [
                'label' => 'Product carousel',
                'group' => 'Shop',
                'icon' => 'box',
                'description' => 'Horizontally scrolling product rail.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'New in the workshop'],
                    'limit' => ['type' => 'number', 'label' => 'How many', 'default' => 8, 'min' => 2, 'max' => 24],
                ],
            ],
            'category_grid' => [
                'label' => 'Category grid',
                'group' => 'Shop',
                'icon' => 'grid',
                'description' => 'Links to product categories.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Browse by room'],
                    'limit' => ['type' => 'number', 'label' => 'How many', 'default' => 4, 'min' => 1, 'max' => 12],
                ],
            ],
            'quote_form' => [
                'label' => 'Quote form',
                'group' => 'Shop',
                'icon' => 'document',
                'description' => 'Captures a lead with a project brief.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Tell us about your space'],
                    'body' => ['type' => 'textarea', 'label' => 'Intro copy', 'default' => 'Send the room, the dimensions and the look you have in mind. We reply within one working day.'],
                    'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Send enquiry'],
                ],
            ],
            'whatsapp_cta' => [
                'label' => 'WhatsApp CTA',
                'group' => 'Shop',
                'icon' => 'phone',
                'description' => 'Starts a WhatsApp conversation.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Prefer to chat?'],
                    'body' => ['type' => 'text', 'label' => 'Body', 'default' => 'Message the studio on WhatsApp — we usually reply within the hour.'],
                    'button_label' => ['type' => 'text', 'label' => 'Button label', 'default' => 'Open WhatsApp'],
                    'number' => ['type' => 'text', 'label' => 'Number override', 'default' => ''],
                ],
            ],

            /* ------------------------------------------------------------- Studio */
            'testimonials' => [
                'label' => 'Testimonials',
                'group' => 'Studio',
                'icon' => 'star',
                'description' => 'Client quotes from the testimonials library.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'What clients say'],
                    'limit' => ['type' => 'number', 'label' => 'How many', 'default' => 3, 'min' => 1, 'max' => 9],
                ],
            ],
            'project_gallery' => [
                'label' => 'Project gallery',
                'group' => 'Studio',
                'icon' => 'image',
                'description' => 'Completed installations from the showroom.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Recent installations'],
                    'limit' => ['type' => 'number', 'label' => 'How many', 'default' => 6, 'min' => 1, 'max' => 18],
                ],
            ],
            'services_list' => [
                'label' => 'Services',
                'group' => 'Studio',
                'icon' => 'wrench',
                'description' => 'What the studio offers.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'What we do'],
                    'limit' => ['type' => 'number', 'label' => 'How many', 'default' => 4, 'min' => 1, 'max' => 12],
                ],
            ],
            'blog_list' => [
                'label' => 'Journal',
                'group' => 'Studio',
                'icon' => 'pencil',
                'description' => 'Latest published articles.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'From the journal'],
                    'limit' => ['type' => 'number', 'label' => 'How many', 'default' => 3, 'min' => 1, 'max' => 9],
                ],
            ],
            'appointment_form' => [
                'label' => 'Appointment form',
                'group' => 'Studio',
                'icon' => 'calendar',
                'description' => 'Books a showroom visit or site survey.',
                'attrs' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading', 'default' => 'Book a showroom visit'],
                    'body' => ['type' => 'textarea', 'label' => 'Intro copy', 'default' => 'Pick a time and we will confirm by phone. Visits take about 45 minutes.'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public static function get(string $type): ?array
    {
        return self::all()[$type] ?? null;
    }

    public static function exists(string $type): bool
    {
        return isset(self::all()[$type]);
    }

    /** Default attribute set for a type, used when a block is first placed. */
    public static function defaults(string $type): array
    {
        $definition = self::get($type);

        if (! $definition) {
            return [];
        }

        $defaults = [];
        foreach ($definition['attrs'] as $name => $attr) {
            $defaults[$name] = $attr['default'] ?? null;
        }

        return $defaults;
    }

    /**
     * Grouped catalogue for the builder's "add block" panel. Rendered preview
     * markup is attached so the canvas can draw a block without a round trip.
     */
    public static function library(): array
    {
        $groups = [];

        foreach (self::GROUPS as $group) {
            $groups[$group] = [];
        }

        foreach (self::all() as $type => $definition) {
            $groups[$definition['group']][$type] = [
                'label' => $definition['label'],
                'description' => $definition['description'],
                'icon' => $definition['icon'],
                'container' => $definition['container'] ?? false,
                'attrs' => $definition['attrs'],
            ];
        }

        return array_filter($groups);
    }

    /**
     * Build a fresh block instance with a stable, sortable id.
     */
    public static function make(string $type, array $attrs = [], ?string $id = null): array
    {
        return [
            'id' => $id ?: 'blk_'.Str::lower(Str::random(10)),
            'type' => $type,
            'attrs' => array_merge(self::defaults($type), $attrs),
            'hidden' => false,
            'children' => [],
        ];
    }

    /**
     * Coerce arbitrary request input into the canonical block tree shape.
     *
     * Anything not in the registry is dropped, unknown attributes are discarded,
     * and children are only kept for container blocks — so malformed or hostile
     * payloads cannot reach the database.
     */
    public static function normalize(mixed $blocks, int $depth = 0): array
    {
        if (! is_array($blocks) || $depth > 4) {
            return [];
        }

        $normalized = [];

        foreach ($blocks as $block) {
            if (! is_array($block) || ! isset($block['type']) || ! self::exists($block['type'])) {
                continue;
            }

            $type = $block['type'];
            $definition = self::get($type);

            $attrs = [];
            $incoming = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];

            foreach ($definition['attrs'] as $name => $meta) {
                $attrs[$name] = array_key_exists($name, $incoming)
                    ? self::coerce($meta, $incoming[$name])
                    : ($meta['default'] ?? null);
            }

            $normalized[] = [
                'id' => is_string($block['id'] ?? null) && preg_match('/^[A-Za-z0-9_\-]{4,40}$/', $block['id'])
                    ? $block['id']
                    : 'blk_'.Str::lower(Str::random(10)),
                'type' => $type,
                'attrs' => $attrs,
                'hidden' => (bool) ($block['hidden'] ?? false),
                'children' => ! empty($definition['container'])
                    ? self::normalize($block['children'] ?? [], $depth + 1)
                    : [],
            ];
        }

        return $normalized;
    }

    private static function coerce(array $meta, mixed $value): mixed
    {
        return match ($meta['type']) {
            'number' => max(
                (int) ($meta['min'] ?? 0),
                min((int) ($meta['max'] ?? 999), (int) $value),
            ),
            'boolean' => (bool) $value,
            'media', 'category' => filled($value) ? (int) $value : null,
            'align' => in_array($value, ['left', 'center', 'right'], true) ? $value : 'left',
            'select' => array_key_exists((string) $value, $meta['options'] ?? []) ? (string) $value : ($meta['default'] ?? null),
            'repeater' => self::coerceRepeater($meta, $value),
            'code' => self::sanitizeHtml((string) $value),
            'textarea', 'text', 'url' => trim((string) $value),
            default => $value,
        };
    }

    /** @return array<int, array<string, string>> */
    private static function coerceRepeater(array $meta, mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $rows = [];

        foreach (array_slice($value, 0, 30) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $clean = [];
            foreach (array_keys($meta['fields'] ?? []) as $field) {
                $clean[$field] = trim((string) ($row[$field] ?? ''));
            }

            if (implode('', $clean) !== '') {
                $rows[] = $clean;
            }
        }

        return $rows;
    }

    /**
     * Allow a small, safe subset of HTML for the custom block.
     *
     * Scripts, event handlers, iframes and javascript: URLs are removed; embeds
     * that need them should be added as a purpose-built block instead.
     */
    public static function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|form|link|meta)\b.*?<\s*/\s*\1\s*>#is', '', $html);
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|form|link|meta)\b[^>]*>#is', '', (string) $html);
        $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', (string) $html);
        $html = preg_replace('#(href|src)\s*=\s*("|\')?\s*javascript:[^"\'>\s]*#i', '$1="#"', (string) $html);

        return trim((string) $html);
    }

    /** Options for editor selects that point at real records. */
    public static function productOptions(): array
    {
        return Product::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    public static function categoryOptions(): array
    {
        return Category::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    /** Every collection a block view may need, loaded once per request. */
    public static function renderContext(array $block): array
    {
        return ['block' => $block];
    }
}
