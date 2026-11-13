@props(['name' => 'grid', 'class' => 'size-4'])
@php
$paths = [
    'grid' => '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>',
    'file' => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4"/>',
    'image' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="m4 17 5-4 4 3 3-2 4 3"/>',
    'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'box' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
    'document' => '<path d="M7 3h7l4 4v14H7z"/><path d="M9 12h6M9 16h6"/>',
    'cart' => '<circle cx="9" cy="19" r="1.6"/><circle cx="17" cy="19" r="1.6"/><path d="M3 4h2l2.5 10h10L20 7H6"/>',
    'users' => '<circle cx="9" cy="9" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 6a3 3 0 0 1 0 6"/><path d="M17 20a5 5 0 0 0-2-4"/>',
    'spark' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>',
    'layers' => '<path d="m12 3 8 4-8 4-8-4z"/><path d="m4 12 8 4 8-4"/><path d="m4 16.5 8 4 8-4"/>',
    'wrench' => '<path d="M14.5 6.5a4 4 0 0 0 5 5L21 10l-1-4-4-1z"/><path d="m13 8-8.5 8.5a2 2 0 1 0 3 3L16 11"/>',
    'calendar' => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M8 3v4M16 3v4M3.5 10h17"/>',
    'chat' => '<path d="M4 5h16v11H9l-5 4z"/>',
    'pencil' => '<path d="m4 20 4-1 10-10-3-3L5 16z"/><path d="m14 4 3 3"/>',
    'star' => '<path d="m12 4 2.4 5 5.6.8-4 4 1 5.5-5-2.7-5 2.7 1-5.5-4-4 5.6-.8z"/>',
    'cog' => '<circle cx="12" cy="12" r="3"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/>',
    'shield' => '<path d="M12 3 5 6v6c0 4 3 7 7 9 4-2 7-5 7-9V6z"/>',
    'external' => '<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M18 14v6H4V6h6"/>',
    'plus' => '<path d="M12 5v14M5 12h14"/>',
    'search' => '<circle cx="11" cy="11" r="6"/><path d="m16 16 4 4"/>',
    'phone' => '<path d="M7 3h3l2 5-2.5 1.5a11 11 0 0 0 5 5L16 12l5 2v3a2 2 0 0 1-2.2 2A16 16 0 0 1 5 5.2 2 2 0 0 1 7 3z"/>',
    'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6 8.5-6"/>',
    'note' => '<path d="M6 3h12v18H6z"/><path d="M9 8h6M9 12h6M9 16h4"/>',
    'arrow-path' => '<path d="M4 12a8 8 0 0 1 13.7-5.7L20 8.5"/><path d="M20 4v4.5h-4.5"/><path d="M20 12a8 8 0 0 1-13.7 5.7L4 15.5"/><path d="M4 20v-4.5h4.5"/>',
    'trash' => '<path d="M5 7h14"/><path d="M9 7V4h6v3"/><path d="M7 7l1 13h8l1-13"/>',
    'check' => '<path d="m5 13 4 4L19 7"/>',
    'eye' => '<path d="M2.5 12S6 6 12 6s9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/><circle cx="12" cy="12" r="2.6"/>',
    'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 8v4.5l3 1.8"/>',
    'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
    'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
    'banknote' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
    'truck' => '<path d="M3 7h10v9H3z"/><path d="M13 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
    'chart' => '<path d="M4 20V4"/><path d="M4 20h16"/><path d="m7 15 4-5 3 3 4-6"/>',
    'tag' => '<path d="M4 4h7l9 9-7 7-9-9z"/><circle cx="8" cy="8" r="1.3"/>',
    'globe' => '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17M12 3.5c2.5 2.4 2.5 14.6 0 17M12 3.5c-2.5 2.4-2.5 14.6 0 17"/>',
];
@endphp
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $paths[$name] ?? $paths['grid'] !!}
</svg>
