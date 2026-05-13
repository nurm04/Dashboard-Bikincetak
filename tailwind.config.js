import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [require('daisyui')],

    daisyui: {
        themes: [
            {
                light: {
                    "primary": "#3885f8",
                    "primary-content": "#ffffff",
                    "secondary": "#f6d860",
                    "accent": "#37cdbe",
                    "neutral": "#3d4451",
                    "base-100": "#ffffff",
                    "base-200": "#f8fafc",
                    "base-300": "#f1f5f9",
                    "base-content": "#1e293b",
                },
                dark: {
                    "primary": "#3885f8",
                    "primary-content": "#ffffff",
                    "secondary": "#f6d860",
                    "accent": "#37cdbe",
                    "neutral": "#2a323c",
                    "base-100": "#1d232a",
                    "base-200": "#191e24",
                    "base-300": "#15191e",
                    "base-content": "#a6adbb",
                }
            }
        ],
        darkTheme: "dark",
        base: true,
        utils: true,
        logs: false,
        themeRoot: ":root",
    },
};
