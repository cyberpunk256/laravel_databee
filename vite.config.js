import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import eslintPlugin from 'vite-plugin-eslint'

export default defineConfig({
  define: {
    'process.env': {}
  },
  plugins: [
    laravel({
      input: [
        'resources/js/app.js',
        'resources/scss/app.scss',
      ],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    vuetify({ autoImport: true }),
    // eslintPlugin(),
  ],
})
