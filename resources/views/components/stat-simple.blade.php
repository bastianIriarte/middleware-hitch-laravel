@props(['label', 'color' => 'gray'])

@php
    $colors = [
        'green' => 'text-green-700 bg-green-50 border-green-200',
        'red' => 'text-red-700 bg-red-50 border-red-200',
        'yellow' => 'text-yellow-700 bg-yellow-50 border-yellow-200',
        'blue' => 'text-blue-700 bg-blue-50 border-blue-200',
        'purple' => 'text-purple-700 bg-purple-50 border-purple-200',
        'gray' => 'text-gray-700 bg-gray-50 border-gray-200',
    ];
@endphp

<div class="p-6 border rounded-xl {{ $colors[$color] }}">
    <div class="text-3xl font-bold mb-1">
        {{ $slot }}
    </div>
    <div class="text-sm font-medium">
        {{ $label }}
    </div>
</div>
