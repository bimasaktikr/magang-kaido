import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/filament/**/*.blade.php',             // ✅ add
        './vendor/filament/**/*.php',                   // ✅ add (components w/ class strings)
        './app/Filament/**/*.php',                      // ✅ add (your pages/resources)
        './app/Livewire/**/*.php',                      // ✅ add
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.php',                         // ✅ if you concat class strings in PHP
        './resources/**/*.js',
        './resources/**/*.ts',                          // ✅ if any TS
        './resources/**/*.vue',
        './resources/**/*.jsx',                         // (if any)
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
