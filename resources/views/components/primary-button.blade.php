<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm rounded-xl transition active:scale-95 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
