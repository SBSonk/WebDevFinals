@props(['type' => 'info', 'dismissible' => true])

@php
    $bgColor = match($type) {
        'success' => 'bg-green-50',
        'error' => 'bg-red-50',
        'warning' => 'bg-yellow-50',
        'info' => 'bg-blue-50',
        default => 'bg-blue-50',
    };

    $textColor = match($type) {
        'success' => 'text-green-800',
        'error' => 'text-red-800',
        'warning' => 'text-yellow-800',
        'info' => 'text-blue-800',
        default => 'text-blue-800',
    };

    $accent = match($type) {
        'success' => 'border-green-500',
        'error' => 'border-red-500',
        'warning' => 'border-yellow-500',
        'info' => 'border-blue-500',
        default => 'border-blue-500',
    };

    $id = 'toast-'.uniqid();
@endphp

<div id="{{ $id }}-container" class="fixed inset-x-0 top-0 z-50 flex justify-center pt-4 pointer-events-none">
    <div id="{{ $id }}" class="max-w-sm w-full mx-4 {{ $bgColor }} border-l-4 {{ $accent }} p-4 rounded shadow-lg transform transition-all duration-300 ease-in-out pointer-events-auto">
        <div class="flex items-start">
            <div class="flex-1">
                <p class="font-medium {{ $textColor }}">
                    {{ $slot }}
                </p>
            </div>
            @if($dismissible)
            <button type="button" aria-label="Dismiss" class="ml-3 text-gray-500 hover:text-gray-700" onclick="document.getElementById('{{ $id }}-container')?.remove()">
                <span class="text-2xl">&times;</span>
            </button>
            @endif
        </div>
    </div>
</div>

<script>
    (function(){
        var id = '{{ $id }}';
        var container = document.getElementById(id + '-container');
        if (!container) return;

        // initial show animation
        var el = document.getElementById(id);
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';

        // auto-dismiss after 1 second
        setTimeout(function(){
            if (!container) return;
            // fade out
            el.classList.add('opacity-0');
            el.style.transition = 'opacity 300ms ease, transform 300ms ease';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function(){ container.remove(); }, 350);
        }, 1000);
    })();
</script>
