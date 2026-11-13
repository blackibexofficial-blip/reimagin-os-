@php
    $height = ['sm' => 'h-6', 'md' => 'h-12', 'lg' => 'h-20', 'xl' => 'h-32'][($block['attrs']['size'] ?? 'md')] ?? 'h-12';
@endphp
<div class="{{ $height }}" aria-hidden="true" data-block="spacer"></div>
