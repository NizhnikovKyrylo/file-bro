import { defineConfig } from 'vite';
import postcssNesting from 'postcss-nesting';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';

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
    laravel({
      input: ['resources/css/file-browser.scss', 'resources/js/file-browser.js', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
});