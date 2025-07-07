<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center p-4 bg-primary-color border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest  hover:scale-105 hover:shadow-primary-color transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
