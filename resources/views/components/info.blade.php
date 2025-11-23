@props(['label'])

<div class="flex flex-col space-y-1">
    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">
        {{ $label }}
    </span>

    <div class="text-gray-800 text-sm leading-relaxed">
        {{ $slot }}
    </div>
</div>
