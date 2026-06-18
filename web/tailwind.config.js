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
        cream: '#fff7df',
        linen: '#f8ecd0',
        terracotta: '#ff6b2b',
        clay: '#d93416',
        sage: '#91b36a',
        leaf: '#207a1f',
        cocoa: '#183315',
        ink: '#0f2110',
        green: '#207a1f',
        mint: '#e8f5dc',
        meadow: '#9bd74d',
        forest: '#0f5f22',
        olive: '#8ba341',
        lightgray: '#efe8d2',
        sunshine: '#ffc829',
        mango: '#ff9f2e',
        coral: '#ff7047',
        tomato: '#d51f12',
        caribbean: '#1eb9d3',
        flamingo: '#f36aa5',
      },
      fontFamily: {
        sans: ['ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
        display: ['Impact', 'Haettenschweiler', 'Arial Narrow Bold', 'Arial', 'sans-serif'],
      },
      boxShadow: {
        tropical: '0 24px 70px rgba(15, 95, 34, 0.16)',
      },
    },
  },
  plugins: [],
}
