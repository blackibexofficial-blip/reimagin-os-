@props(['block'])
@php
    /* Every registry type has exactly one view; an unknown type renders nothing
     * rather than throwing, so a stale payload can never take the site down. */
    $type = $block['type'] ?? null;
    $view = $type && view()->exists("site.blocks.{$type}") ? "site.blocks.{$type}" : null;
@endphp
@if ($view)
    @include($view, ['block' => $block])
@endif
