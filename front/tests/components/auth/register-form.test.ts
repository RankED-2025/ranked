import { mount, VueWrapper } from '@vue/test-utils'
import { globalTestPlugins, getByTestId } from '../../util/vuetify-utils'
import RegisterForm from '../../../src/components/auth/RegisterForm.vue'
import { afterEach, beforeEach, describe, vi, it, MockInstance, expect } from 'vitest'
import { useUserStore } from '../../../src/stores/userStore'
import { createPinia, setActivePinia } from 'pinia'
import { VForm } from 'vuetify/components'
import { nextTick } from 'vue'
import { flushPromises } from '@vue/test-utils'

const mountComponent = (): VueWrapper => {
  return mount(RegisterForm, {
    global: {
      plugins: globalTestPlugins
    }
  })
}

describe("RegisterForm component", () => {
  let wrapper: VueWrapper;

  let userStore: ReturnType<typeof useUserStore>
  let userStoreSpy: MockInstance<typeof userStore.registerAttempt>;

  beforeEach(() => {
    setActivePinia(createPinia())
    userStore = useUserStore()
    userStoreSpy = vi.spyOn(userStore, 'registerAttempt').mockResolvedValue(true)
  })

  afterEach(() => {
    vi.clearAllMocks()
    wrapper?.unmount()
  })

  /**
   * Updates the component after setting a field value
   */
  const updateFormAfterDataSet = async () => {
    const formRef: VForm | undefined = wrapper.vm.$refs.formAnchor as VForm | undefined

    // Trigger validation manually — Vuetify won't auto-validate on setValue
    await formRef?.validate()

    await flushPromises()

    // Let Vuetify flush its internal state updates
    await nextTick()
  }

  describe("Form rules", () => {
    it.each([
      { value: '', message: "Veuillez entrer un nom d'utilisateur" },
      { value: null, message: "Veuillez entrer un nom d'utilisateur" },
      { value: 'K', message: "Le nom d'utilisateur doit contenir au moins 3 caractères" },
      { value: 'JA', message: "Le nom d'utilisateur doit contenir au moins 3 caractères" },
    ])("The field 'Username' should have the message $message when the value is $value", async ({ value, message }) => {
      wrapper = mountComponent()

      await wrapper
        .get(getByTestId('name-field'))
        .find('input')
        .setValue(value)

      await updateFormAfterDataSet()

      const field = wrapper
        .get(getByTestId('name-field'))
        .find('.v-messages__message')

      expect(field.exists()).toBe(true)
      expect(field.text()).toBe(message)
    })
  })

  describe("Form submission", () => {

  })
})
