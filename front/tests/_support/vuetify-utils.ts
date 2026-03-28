import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import ResizeObserver from 'resize-observer-polyfill'
import { createRouter, createWebHistory } from 'vue-router'

global.ResizeObserver = ResizeObserver

export const vuetifyInstance = createVuetify({
  components,
  directives,
})

export const testRouter = createRouter({
  history: createWebHistory(),
  routes: [{
    path: '/:pathMatch(.*)*',
    component: {
      template: '<div />'
    }
  }]
})

export const globalTestPlugins = [
  vuetifyInstance,
  testRouter
]

export const getByTestId = (id: string) => `[data-testid='${id}']`
