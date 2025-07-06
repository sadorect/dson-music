@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-white/30 focus:border-primary-color focus:ring-primary-color rounded-md shadow-sm bg-transparent text-white']) }}>
