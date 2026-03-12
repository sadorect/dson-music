@props([
    'track',
    'icon' => true,
])

@if(($track?->duration ?? 0) > 0)
    <span {{ $attributes->class(['inline-flex items-center gap-1 tabular-nums']) }}>
        @if($icon)
            <svg class="h-3.5 w-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
            </svg>
        @endif
        <span>{{ $track->formatted_duration }}</span>
    </span>
@endif
