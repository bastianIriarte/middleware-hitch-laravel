@props([
    'title',
    'icon' => 'circle',
    'date' => null,
    'active' => false,
    'completed' => false,
    'failed' => false,
    'spin' => false,
])

@php
    $classes = "p-6 rounded-lg text-center border";

    if ($failed) {
        $classes .= " bg-red-100 border-red-300 text-red-700";
    } elseif ($completed) {
        $classes .= " bg-green-100 border-green-300 text-green-700";
    } elseif ($active) {
        $classes .= " bg-primary-100 border-primary-300 text-primary-700";
    } else {
        $classes .= " bg-gray-50 border-gray-200 text-gray-600";
    }
@endphp

<div class="{{ $classes }}">
    <div class="flex justify-center mb-3">
        <i data-lucide="{{ $icon }}" 
           class="w-10 h-10 {{ $spin ? 'animate-spin' : '' }}"></i>
    </div>

    <h4 class="font-semibold text-lg">{{ $title }}</h4>

    @if($date)
        <div class="mt-2 font-semibold">{{ $date->format('d/m/Y') }}</div>
        <div class="text-xs text-gray-500">{{ $date->format('H:i:s') }}</div>
    @else
        <div class="mt-2 text-gray-400 text-sm">Pendiente</div>
    @endif
</div>
