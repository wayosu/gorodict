import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        // Filament
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: 'rgb(var(--primary-50))',
                    100: 'rgb(var(--primary-100))',
                    200: 'rgb(var(--primary-200))',
                    300: 'rgb(var(--primary-300))',
                    400: 'rgb(var(--primary-400))',
                    500: 'rgb(var(--primary-500))',
                    600: 'rgb(var(--primary-600))',
                    700: 'rgb(var(--primary-700))',
                    800: 'rgb(var(--primary-800))',
                    900: 'rgb(var(--primary-900))',
                    950: 'rgb(var(--primary-950))',
                },
                danger: {
                    50: 'rgb(var(--danger-50))',
                    100: 'rgb(var(--danger-100))',
                    200: 'rgb(var(--danger-200))',
                    300: 'rgb(var(--danger-300))',
                    400: 'rgb(var(--danger-400))',
                    500: 'rgb(var(--danger-500))',
                    600: 'rgb(var(--danger-600))',
                    700: 'rgb(var(--danger-700))',
                    800: 'rgb(var(--danger-800))',
                    900: 'rgb(var(--danger-900))',
                    950: 'rgb(var(--danger-950))',
                },
                success: {
                    50: 'rgb(var(--success-50))',
                    100: 'rgb(var(--success-100))',
                    200: 'rgb(var(--success-200))',
                    300: 'rgb(var(--success-300))',
                    400: 'rgb(var(--success-400))',
                    500: 'rgb(var(--success-500))',
                    600: 'rgb(var(--success-600))',
                    700: 'rgb(var(--success-700))',
                    800: 'rgb(var(--success-800))',
                    900: 'rgb(var(--success-900))',
                    950: 'rgb(var(--success-950))',
                },
                warning: {
                    50: 'rgb(var(--warning-50))',
                    100: 'rgb(var(--warning-100))',
                    200: 'rgb(var(--warning-200))',
                    300: 'rgb(var(--warning-300))',
                    400: 'rgb(var(--warning-400))',
                    500: 'rgb(var(--warning-500))',
                    600: 'rgb(var(--warning-600))',
                    700: 'rgb(var(--warning-700))',
                    800: 'rgb(var(--warning-800))',
                    900: 'rgb(var(--warning-900))',
                    950: 'rgb(var(--warning-950))',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
