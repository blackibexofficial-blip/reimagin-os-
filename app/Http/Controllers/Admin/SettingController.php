<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** Grouped site settings rendered as one form per tab. */
    private const GROUPS = [
        'brand' => [
            'label' => 'Brand',
            'fields' => [
                'site_name' => ['label' => 'Studio name', 'default' => 'Woodex'],
                'site_tagline' => ['label' => 'Tagline', 'default' => 'Furniture, designed once — then made to measure.'],
                'site_description' => ['label' => 'Short description', 'type' => 'textarea', 'default' => 'Fitted wardrobes, modular kitchens and custom furniture made in Lahore.'],
            ],
        ],
        'contact' => [
            'label' => 'Contact',
            'fields' => [
                'phone' => ['label' => 'Phone', 'default' => '+92 300 4567890'],
                'email' => ['label' => 'Email', 'default' => 'studio@woodex.pk'],
                'whatsapp_number' => ['label' => 'WhatsApp number', 'default' => '923004567890', 'hint' => 'Digits only, with country code.'],
                'address' => ['label' => 'Showroom address', 'default' => '14-C Gulberg III, Lahore'],
                'hours' => ['label' => 'Opening hours', 'default' => 'Monday–Saturday, 11:00 – 19:00'],
            ],
        ],
        'commerce' => [
            'label' => 'Commerce',
            'fields' => [
                'currency' => ['label' => 'Currency', 'default' => 'PKR'],
                'tax_percent' => ['label' => 'Sales tax %', 'default' => '0'],
                'delivery_minor' => ['label' => 'Default delivery charge (minor units)', 'default' => '0'],
                'quotation_validity_days' => ['label' => 'Quotation validity (days)', 'default' => '14'],
            ],
        ],
        'social' => [
            'label' => 'Social',
            'fields' => [
                'instagram_url' => ['label' => 'Instagram URL', 'default' => ''],
                'facebook_url' => ['label' => 'Facebook URL', 'default' => ''],
                'pinterest_url' => ['label' => 'Pinterest URL', 'default' => ''],
            ],
        ],
        'integrations' => [
            'label' => 'Integrations',
            'fields' => [
                'ga_measurement_id' => ['label' => 'Google Analytics ID', 'default' => '', 'hint' => 'e.g. G-XXXXXXX'],
                'meta_pixel_id' => ['label' => 'Meta Pixel ID', 'default' => ''],
            ],
        ],
    ];

    public function index(): View
    {
        return view('admin.settings.index', [
            'groups' => collect(self::GROUPS)->map(fn (array $group, string $key) => [
                'key' => $key,
                'label' => $group['label'],
                'fields' => collect($group['fields'])->map(fn (array $field, string $name) => [
                    'name' => $name,
                    'label' => $field['label'],
                    'type' => $field['type'] ?? 'text',
                    'hint' => $field['hint'] ?? null,
                    'value' => Setting::get($name, $field['default']),
                ])->all(),
            ])->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $group = $request->string('group')->toString();

        /* A missing or renamed tab is a stale form, not a missing page: send the
         * editor back to settings with a readable message rather than a 404. */
        if (! array_key_exists($group, self::GROUPS)) {
            return back()->with('error', 'That settings tab no longer exists. Reload the page and try again.');
        }

        foreach (self::GROUPS[$group]['fields'] as $name => $field) {
            if ($request->has($name)) {
                Setting::put($name, (string) $request->input($name), $group, $field['type'] ?? 'text');
            }
        }

        return back()->with('status', self::GROUPS[$group]['label'].' settings saved.');
    }
}
