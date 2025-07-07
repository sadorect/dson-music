@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-white font-bold']) }}>
    {{ $value ?? $slot }}
</label>
