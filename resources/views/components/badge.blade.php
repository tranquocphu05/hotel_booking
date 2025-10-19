@props([
  'label' => '',
  'bg' => 'bg-gray-100',
  'text' => 'text-gray-700',
  'icon' => null,
  'min' => 'min-w-[120px]',
])

<span {{ $attributes->class("inline-flex items-center justify-center gap-1 px-3 py-1 rounded-full text-xs font-medium leading-none $bg $text $min") }}>
  @if($icon)<i class="fas {{ $icon }}"></i>@endif
  <span>{{ $label }}</span>
</span>
