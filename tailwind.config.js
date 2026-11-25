import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import aspectRatio from '@tailwindcss/aspect-ratio';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
                display: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
            },
            colors: {
                brand: {
                    50: '#fff1f1',
                    100: '#ffe1e1',
                    200: '#ffc7c7',
                    300: '#ffa0a0',
                    400: '#ff6666',
                    500: '#e53535',
                    600: '#cc2222',
                    700: '#ab1818',
                    800: '#8d1717',
                    900: '#751919',
                },
                // Aliased for backward compatibility with existing views
                ember: {
                    300: '#ffa0a0',
                    400: '#ff6666',
                    500: '#e53535',
                    600: '#cc2222',
                },
                tide: {
                    300: '#67e8f9',
                    400: '#22d3ee',
                    500: '#06b6d4',
                },
                coal: {
                    950: '#090a0f',
                    900: '#11131b',
                    800: '#171a25',
                    700: '#212636',
                    600: '#323a4e',
                },
                surface: {
                    ground: '#090a0f',
                    panel: '#11131b',
                    card: '#161924',
                    overlay: '#1d2230',
                },
            },
            boxShadow: {
                subtle: '0 1px 2px 0 rgba(0, 0, 0, 0.4)',
                card: '0 4px 20px -2px rgba(0, 0, 0, 0.5)',
                elevated: '0 12px 36px -4px rgba(0, 0, 0, 0.65)',
                glow: '0 0 30px -5px rgba(229, 53, 53, 0.25)',
            },
            letterSpacing: {
                editorial: '0.2em',
                kicker: '0.15em',
            },
        },
    },
    plugins: [forms, typography, aspectRatio],
};
