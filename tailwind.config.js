import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    bg: '#0b1326',
                    surface: '#131b2e',
                    'surface-high': '#1e293b',
                    primary: '#adc6ff',
                    'primary-dim': '#4b8eff',
                    'on-primary': '#002e69',
                    muted: '#8b90a0',
                    border: '#334155',
                    'border-subtle': '#222a3d',
                },
            },
            fontFamily: {
                sans: ['Inter', 'Instrument Sans', ...defaultTheme.fontFamily.sans],
                display: ['Hanken Grotesk', 'Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.25rem',
                '4xl': '1.5rem',
            },
            boxShadow: {
                soft: '0 1px 3px 0 rgb(0 0 0 / 0.08), 0 1px 2px -1px rgb(0 0 0 / 0.06), 0 0 0 1px rgb(0 0 0 / 0.02)',
                glow: '0 0 24px -4px rgba(173, 198, 255, 0.25)',
                'glow-sm': '0 0 12px -2px rgba(173, 198, 255, 0.2)',
                panel: 'inset 0 1px 0 0 rgba(255, 255, 255, 0.04), 0 4px 24px -4px rgba(0, 0, 0, 0.4)',
            },
            backgroundImage: {
                'grid-pattern': 'linear-gradient(rgba(173,198,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(173,198,255,0.03) 1px, transparent 1px)',
            },
            backgroundSize: {
                grid: '32px 32px',
            },
        },
    },

    plugins: [forms],
};
