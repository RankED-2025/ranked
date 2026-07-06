import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { VForm } from 'vuetify/components'
import ResizeObserver from 'resize-observer-polyfill'
import { createMemoryHistory, createRouter } from 'vue-router'
import { nextTick } from 'vue'
import { flushPromises, type VueWrapper } from '@vue/test-utils'

global.ResizeObserver = ResizeObserver

export const vuetifyInstance = createVuetify({
  components,
  directives,
})

export const testRouter = createRouter({
  history: createMemoryHistory(),
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

/**
 * Triggers a Vuetify form's validation (not automatic on setValue) and flushes
 * the resulting pending promises/state updates.
 */
export const validateVuetifyForm = async (wrapper: VueWrapper, refName: string) => {
  const formRef = wrapper.vm.$refs[refName] as VForm | undefined

  await formRef?.validate()
  await flushPromises()
  await nextTick()
}
