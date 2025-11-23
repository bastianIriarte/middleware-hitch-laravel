@props(['value' => 0, 'icon' => 'bar-chart', 'color' => 'primary'])

@php
    $colors = [
        'primary' => 'from-primary-500 to-secondary-500',
        'blue'    => 'from-blue-500 to-blue-700',
        'green'   => 'from-green-500 to-emerald-600',
        'rose'    => 'from-rose-400 to-red-500',
        'yellow'  => 'from-yellow-400 to-orange-400',
        'purple'  => 'from-purple-500 to-indigo-600',
    ];
@endphp

<div class="rounded-xl text-white shadow-lg p-6 bg-gradient-to-br {{ $colors[$color] ?? $colors['primary'] }}">
    <div class="flex items-center gap-3 mb-3">
        <i data-lucide="{{ $icon }}" class="w-8 h-8"></i>
        <span class="text-xl font-semibold">{{ $slot }}</span>
    </div>

    <div class="text-5xl font-bold leading-none">
        {{ number_format($value ?? 0) }}
    </div>
</div>
