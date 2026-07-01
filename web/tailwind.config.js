/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './app/Livewire/**/*.php',
    './vendor/livewire/livewire/dist/*.js',
  ],
  theme: {
    extend: {
      colors: {
        cream: '#ffffff',
        linen: '#f5f5f5',
        terracotta: '#ff5a00',
        clay: '#e64a00',
        sage: '#ff8a4c',
        leaf: '#ff5a00',
        cocoa: '#111111',
        ink: '#000000',
        green: '#ff5a00',
        mint: '#fff1e8',
        meadow: '#ff6a00',
        forest: '#000000',
        olive: '#ff8a4c',
        lightgray: '#e5e5e5',
        sunshine: '#ff6a00',
        mango: '#e64a00',
        coral: '#ff5a00',
        tomato: '#e64a00',
        caribbean: '#ff7a1a',
        flamingo: '#ff8a4c',
      },
      fontFamily: {
        sans: ['Segoe UI', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['Segoe UI', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        tropical: '0 24px 70px rgba(0, 0, 0, 0.16)',
      },
    },
  },
  plugins: [],
}
