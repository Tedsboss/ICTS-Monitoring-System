/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    corePlugins: {
        // Prevent Tailwind from resetting existing Bootstrap / Argon styles
        preflight: false,
    },

    theme: {
        extend: {},
    },

    plugins: [],
};
