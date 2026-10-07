import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    base: '#FAFAFA',
                    dark: '#181A1B',
                    primary: '#0D9488',
                    warning: '#EA580C',
                    success: '#10B981',
                    danger: '#EF4444',
                }
            }
        },
    },

    plugins: [forms],
};
