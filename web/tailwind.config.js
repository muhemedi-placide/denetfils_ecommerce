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
        cream: '#f8f3ea',
        linen: '#fffaf2',
        terracotta: '#b85c38',
        clay: '#8f3f24',
        sage: '#6f8f72',
        leaf: '#2f5d3a',
        cocoa: '#4b3528',
        ink: '#1d1713',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
