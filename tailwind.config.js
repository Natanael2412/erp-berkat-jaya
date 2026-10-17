import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                gunmetal: {
                    DEFAULT: '#181A1B',
                    50: '#F4F5F6',
                    100: '#E6E8EA',
                    700: '#2A2D2F',
                    800: '#212426',
                    900: '#181A1B',
                    950: '#0F1011',
                },
                brand: {
                    base: '#FAFAFA',
                    dark: '#181A1B',
                    primary: '#0D9488',
                    warning: '#EA580C',
                    success: '#10B981',
                    danger: '#EF4444',
                    // Warna persis dari logo Berkat Jaya
                    teal: '#088F82',
                    'teal-deep': '#066B62',
                    orange: '#F47316',
                    ink: '#141414',
                },
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(18px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'float-slow': {
                    '0%,100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                'bolt-glow': {
                    '0%,100%': { opacity: '.55', transform: 'scale(1)' },
                    '50%': { opacity: '1', transform: 'scale(1.06)' },
                },
            },
            animation: {
                'fade-up': 'fade-up .7s cubic-bezier(.2,.7,.2,1) both',
                'float-slow': 'float-slow 6s ease-in-out infinite',
                'bolt-glow': 'bolt-glow 3.2s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
