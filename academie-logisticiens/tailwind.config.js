import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#E8EDF4',
                    100: '#D1DBE9',
                    200: '#A3B7D3',
                    300: '#7593BD',
                    400: '#476FA7',
                    500: '#1B3A5C',
                    600: '#152E4A',
                    700: '#0B1E36',
                    800: '#081525',
                    900: '#040B13',
                },
                accent: {
                    50:  '#F9F3E6',
                    100: '#F0E3C4',
                    200: '#E5D09E',
                    300: '#DABB78',
                    400: '#CFAB5A',
                    500: '#CFAB5A',
                    600: '#B8944A',
                    700: '#8A6E37',
                },
                surface: {
                    light:  '#F7F8FA',
                    dark:   '#161B22',
                    darker: '#0D1117',
                },
            },
            fontFamily: {
                heading: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                body:    ['Inter', ...defaultTheme.fontFamily.sans],
            },
            animation: {
                'marquee-partners': 'marquee-partners 30s linear infinite',
            },
            keyframes: {
                'marquee-partners': {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
            },
        },
    },

    plugins: [forms],
};
