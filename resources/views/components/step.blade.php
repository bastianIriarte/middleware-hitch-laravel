@props([
    'icon' => 'circle',
    'title',
    'checked' => false,
    'failed' => false,
    'loading' => false,
    'date' => null,
])

@php
    // Color según estado (compatible con PHP 7/8)
    if ($failed) {
        $stateClass = 'border-red-300 bg-red-50 text-red-700';
    } elseif ($loading) {
        $stateClass = 'border-yellow-300 bg-yellow-50 text-yellow-700';
    } elseif ($checked) {
        $stateClass = 'border-green-300 bg-green-50 text-green-700';
    } else {
        $stateClass = 'border-gray-200 bg-gray-50 text-gray-600';
    }
@endphp

<div class="border rounded-xl p-5 {{ $stateClass }}">

    <div class="flex items-center gap-2 mb-3">
        <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $loading ? 'animate-spin' : '' }}"></i>
        <span class="font-semibold">{{ $title }}</span>
    </div>

    @if($date)
        <div class="text-sm">
            <div class="font-medium">{{ $date->format('d/m/Y') }}</div>
            <div class="text-xs">{{ $date->format('H:i:s') }}</div>
        </div>
    @else
        <div class="text-xs opacity-70">Pendiente</div>
    @endif

</div>
