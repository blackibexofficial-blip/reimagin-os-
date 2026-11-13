@props(['action', 'method' => 'DELETE', 'label' => 'Delete', 'message' => 'Are you sure?', 'variant' => 'danger', 'icon' => 'trash'])
<form method="POST" action="{{ $action }}"
      x-data
      @submit.prevent="if (confirm(@js($message))) $el.submit()">
    @csrf
    @method($method)
    <x-ui.button :variant="$variant" :icon="$icon" type="submit">{{ $label }}</x-ui.button>
</form>
