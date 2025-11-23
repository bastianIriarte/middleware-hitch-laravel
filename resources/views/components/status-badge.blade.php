@props(['status'])

@if($status === 'uploaded')
    <span class="inline-flex items-center space-x-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
        <i data-lucide="check-circle" class="w-3 h-3"></i>
        <span>Subido</span>
    </span>
@elseif($status === 'failed')
    <span class="inline-flex items-center space-x-1 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
        <i data-lucide="x-circle" class="w-3 h-3"></i>
        <span>Fallido</span>
    </span>
@elseif($status === 'processing')
    <span class="inline-flex items-center space-x-1 px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
        <i data-lucide="loader" class="w-3 h-3 animate-spin"></i>
        <span>Procesando</span>
    </span>
@else
    <span class="inline-flex items-center space-x-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
        <i data-lucide="inbox" class="w-3 h-3"></i>
        <span>Recibido</span>
    </span>
@endif
