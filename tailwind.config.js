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
                },
            },
        },
    },

    plugins: [forms],
};
