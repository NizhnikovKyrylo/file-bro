import { defineConfig } from 'vite';
import postcssNesting from 'postcss-nesting';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  css: {
    postcss: {
      plugins: [
        autoprefixer({}),
        postcssNesting
      ],
    },
  },
  plugins: [
    vue(),
    laravel({
      input: ['resources/css/file-browser.scss', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
});