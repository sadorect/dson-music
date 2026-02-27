import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './app/Providers/Filament/**/*.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: { 
                    DEFAULT: '#ef4444', 
                    dark: '#dc2626' 
                },
                glass: {
                    base: 'rgba(255, 255, 255, 0.18)',
                    hover: 'rgba(255, 255, 255, 0.28)',
                    border: 'rgba(255, 255, 255, 0.25)',
                },
            },
            backdropBlur: { 
                xs: '2px', 
                '3xl': '32px' 
            },
            boxShadow: {
                glass: '0 8px 32px rgba(0, 0, 0, 0.15)',
                'glass-hover': '0 12px 40px rgba(0, 0, 0, 0.22)',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
            },
        },
    },

    plugins: [forms],
};
