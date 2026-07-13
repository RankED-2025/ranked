import { fileURLToPath, URL } from 'node:url'
import { mergeConfig, defineConfig, configDefaults } from 'vitest/config'
import viteConfig from './vite.config'

export default mergeConfig(
  viteConfig,
  defineConfig({
    test: {
      environment: 'jsdom',
      environmentOptions: {
        jsdom: {
          url: 'http://localhost/',
        },
      },
      setupFiles: ['./tests/setup/localStorage.ts'],
      watch: false,
      exclude: [...configDefaults.exclude, 'e2e/**'],
      root: fileURLToPath(new URL('./', import.meta.url)),
      coverage: {
        provider: 'v8',
        reporter: ['text', 'lcov'],
        reportsDirectory: './coverage',
        exclude: [...(configDefaults.coverage.exclude ?? []), 'tests/**', 'public/**', 'src/router/**', 'src/main.ts'],
      },
      css: false,

      server: {
        deps: {
          inline: ['vuetify']
        }
      }
    },
  }),
)
