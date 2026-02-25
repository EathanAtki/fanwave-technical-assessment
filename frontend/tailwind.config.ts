import type { Config } from 'tailwindcss';

export default <Partial<Config>>{
  content: [
    './components/**/*.{vue,js,ts}',
    './pages/**/*.vue',
    './app.vue',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f3f7f7',
          500: '#0b7a75',
          700: '#085651',
        },
      },
    },
  },
};
