<button {{ $attributes->merge(['type' => 'submit', 'class' => 'items-center justify-center p-4 bg-primary-color text-white border border-transparent rounded-full w-full font-semibold text-xs uppercase hover:scale-105 hover:shadow-md hover:shadow-orange-300 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
