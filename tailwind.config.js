import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import colors from "tailwindcss/colors";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
        "./resources/js/**/*.vue",
    ],

    safelist: [
        // === Warna Error Page ===
        "bg-yellow-50",
        "bg-yellow-100",
        "bg-yellow-200",
        "bg-yellow-300",
        "text-yellow-600",
        "text-yellow-700",

        "bg-amber-50",
        "bg-amber-100",
        "bg-amber-200",
        "bg-amber-600",
        "bg-amber-700",
        "text-amber-600",
        "text-amber-700",

        "bg-emerald-50",
        "border-emerald-200",
        "text-emerald-600",
        "text-emerald-700",

        "bg-red-50",
        "bg-red-100",
        "bg-red-200",
        "text-red-600",
        "text-red-700",
        "text-red-900",

        "bg-gray-50",
        "bg-slate-600",
        "bg-gray-100",
        "text-gray-600",
        "text-gray-700",
        "text-gray-800",

        // === Utilities umum ===
        "bg-white",
        "hover:bg-gray-50",
        "hover:bg-amber-700",
        "hover:bg-yellow-600",
        "hover:bg-primary_hover",
        "hover:-translate-y-1",
        "shadow-md",
        "shadow-lg",
    ],

    theme: {
        extend: {
            fontFamily: {
                poppins: ["Poppins", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ...colors,

                primary: "#2458D3",
                primary_hover: "#1D4ED8",

                secondary: "#4FC3E8",

                success: "#57C84D",

                danger: "#DC2626",

                background: "#F8FAFC",

                card: "#FFFFFF",

                border: "#E2E8F0",

                text: "#1E293B",
            },
        },
    },

    plugins: [forms],
};
