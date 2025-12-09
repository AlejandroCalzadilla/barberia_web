import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Colores personalizados usando variables CSS
                primary: {
                    DEFAULT: 'var(--color-primary)',
                    50: 'color-mix(in srgb, var(--color-primary) 10%, white)',
                    100: 'color-mix(in srgb, var(--color-primary) 20%, white)',
                    200: 'color-mix(in srgb, var(--color-primary) 40%, white)',
                    300: 'color-mix(in srgb, var(--color-primary) 60%, white)',
                    400: 'color-mix(in srgb, var(--color-primary) 80%, white)',
                    500: 'var(--color-primary)',
                    600: 'color-mix(in srgb, var(--color-primary) 80%, black)',
                    700: 'color-mix(in srgb, var(--color-primary) 60%, black)',
                    800: 'color-mix(in srgb, var(--color-primary) 40%, black)',
                    900: 'color-mix(in srgb, var(--color-primary) 20%, black)',
                },
                secondary: {
                    DEFAULT: 'var(--color-secondary)',
                },
                accent: {
                    DEFAULT: 'var(--color-accent)',
                },
                neutral: {
                    DEFAULT: 'var(--color-neutral)',
                },
                'base-100': 'var(--color-base)',
                'base-200': 'color-mix(in srgb, var(--color-base) 95%, black)',
                'base-300': 'color-mix(in srgb, var(--color-base) 90%, black)',
                info: 'var(--color-info)',
                success: 'var(--color-success)',
                warning: 'var(--color-warning)',
                error: 'var(--color-error)',
            }
        },
    },

    plugins: [forms, typography],
};
