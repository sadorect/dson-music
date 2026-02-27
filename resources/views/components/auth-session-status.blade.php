@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm font-medium']) }}>
        {{ $status }}
    </div>
@endif
