export default defineNuxtConfig({
  compatibilityDate: '2026-02-25',
  devtools: { enabled: false },
  css: ['~/assets/css/main.css'],
  modules: ['@nuxtjs/tailwindcss'],
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost',
    },
  },
  typescript: {
    strict: true,
    typeCheck: false,
  },
});
