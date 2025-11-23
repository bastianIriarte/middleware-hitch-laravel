@props(['level'])

@php
    $class = 'bg-gray-100 text-gray-700';
    $text  = 'Baja';

    if ($level === 'critical') {
        $class = 'bg-red-100 text-red-700';
        $text  = 'Crítica';
    } elseif ($level === 'high') {
        $class = 'bg-yellow-100 text-yellow-800';
        $text  = 'Alta';
    } elseif ($level === 'medium') {
        $class = 'bg-blue-100 text-blue-700';
        $text  = 'Media';
    }
@endphp

<span class="px-2 py-1 rounded text-xs font-medium {{ $class }}">
    {{ $text }}
</span>
