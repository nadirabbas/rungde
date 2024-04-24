/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./client/**/*.{js,ts,jsx,tsx,vue}",
  ],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary)',
        secondary: 'var(--color-secondary)',
      }
    },
  },
  plugins: [],
}

