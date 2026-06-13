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
        linen: '#f8f8f8',
        terracotta: '#ff8a00',
        clay: '#e67600',
        sage: '#58b400',
        leaf: '#4ea300',
        cocoa: '#333333',
        ink: '#1f1f1f',
        orange: '#ff8a00',
        green: '#58b400',
        lightgray: '#eeeeee',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
