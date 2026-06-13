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
        linen: '#f7fbf4',
        terracotta: '#4fb000',
        clay: '#3f8f00',
        sage: '#6fbd2b',
        leaf: '#2f7d1b',
        cocoa: '#253322',
        ink: '#121a10',
        green: '#4fb000',
        mint: '#eaf7df',
        meadow: '#8ed957',
        forest: '#1f5f16',
        olive: '#6f8f2a',
        lightgray: '#edf3ea',
      },
      fontFamily: {
        sans: ['ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
