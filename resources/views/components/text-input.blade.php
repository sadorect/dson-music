@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-black/20 focus:border-primary-color focus:ring-primary-color rounded-md shadow-sm bg-white text-black']) }}>
