const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./src/Resources/**/*.blade.php", "./src/Resources/**/*.js"],

    theme: {
        container: {
            center: true,

            screens: {
                "4xl": "1920px",
            },

            padding: {
                DEFAULT: "16px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            "3xl": "1680px",
            "4xl": "1920px",
        },

        extend: {
            colors: {
                brandColor: "var(--brand-color)",
                primary: "var(--brand-color)",
                secondary: colors.gray[600],
                success: colors.green[600],
                warning: colors.amber[500],
                danger: colors.red[600],
                info: colors.blue[600],
                muted: colors.gray[400],
            },

            fontFamily: {
                sans: ['Inter', 'Roboto', 'Lato', 'Open Sans', 'system-ui', '-apple-system', 'sans-serif'],
                inter: ['Inter'],
                icon: ['icomoon']
            }
        },
    },
    
    darkMode: 'class',

    plugins: [],

    safelist: [
        {
            pattern: /icon-/,
        }
    ]
};