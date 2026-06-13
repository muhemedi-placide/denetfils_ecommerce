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
        cream: '#FFF7E6',
        linen: '#FFFCF4',
        terracotta: '#E73323',
        clay: '#B51F16',
        sage: '#F8B400',
        leaf: '#166534',
        cocoa: '#1F1712',
        ink: '#120D0A',
        chili: '#E73323',
        gold: '#F8B400',
        plantain: '#F6D35B',
        lakay: '#166534',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
