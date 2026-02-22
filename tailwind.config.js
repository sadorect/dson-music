import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                "primary-color": "#f97316",
                bg: "#121212",
                // Glass/Metallic palette
                glass: {
                    50: "rgba(255, 255, 255, 0.05)",
                    100: "rgba(255, 255, 255, 0.08)",
                    200: "rgba(255, 255, 255, 0.12)",
                    300: "rgba(255, 255, 255, 0.15)",
                    400: "rgba(255, 255, 255, 0.20)",
                    500: "rgba(255, 255, 255, 0.25)",
                },
                metal: {
                    light: "#e8eaed",
                    medium: "#9aa0a6",
                    dark: "#5f6368",
                },
            },
            backdropBlur: {
                xs: "2px",
                sm: "4px",
                md: "8px",
                lg: "12px",
                xl: "16px",
            },
            boxShadow: {
                "glass-sm": "0 2px 8px rgba(0, 0, 0, 0.1)",
                glass: "0 4px 16px rgba(0, 0, 0, 0.15)",
                "glass-lg": "0 8px 32px rgba(0, 0, 0, 0.2)",
                "metal-sm":
                    "inset 0 1px 0 rgba(255, 255, 255, 0.3), 0 1px 2px rgba(0, 0, 0, 0.1)",
                metal: "inset 0 1px 0 rgba(255, 255, 255, 0.3), 0 2px 8px rgba(0, 0, 0, 0.15)",
                "metal-lg":
                    "inset 0 2px 4px rgba(255, 255, 255, 0.3), 0 8px 16px rgba(0, 0, 0, 0.2)",
            },
            borderColor: {
                glass: "rgba(255, 255, 255, 0.1)",
                "glass-light": "rgba(255, 255, 255, 0.2)",
                metal: "rgba(255, 255, 255, 0.15)",
            },
        },
    },

    plugins: [forms],
};
