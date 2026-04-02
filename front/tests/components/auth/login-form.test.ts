import { afterEach, describe, vi, beforeEach, MockInstance, it, expect } from 'vitest'
import { flushPromises, mount, VueWrapper } from '@vue/test-utils'
import { getByTestId, globalTestPlugins } from '../../util/vuetify-utils'
import LoginForm from '../../../src/components/auth/LoginForm.vue'
import { createPinia, setActivePinia } from 'pinia'
import { useUserStore } from '../../../src/stores/userStore'
import { VForm } from 'vuetify/components'
import { nextTick } from 'vue'

const mountComponent = (): VueWrapper => {
  return mount(LoginForm, {
    global: {
      plugins: globalTestPlugins
    }
  })
}

type FormFieldValues = {
  email?: string|null,
  password?: string|null
}

// ------------------------------------------------------------------

describe("LoginForm Component", () => {
  let wrapper: VueWrapper;
  let userStore: ReturnType<typeof useUserStore>
  let userStoreSpy: MockInstance<typeof userStore.loginAttempt>;

  beforeEach(() => {
    setActivePinia(createPinia())
    userStore = useUserStore()
    userStoreSpy = vi.spyOn(userStore, 'loginAttempt').mockResolvedValue(true)
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

  const setFieldValues = async (
    email?: string|null, password?: string|null
  ) => {
    await wrapper
      .get(getByTestId('email-field'))
      .find('input')
      .setValue(email)

    await wrapper
      .get(getByTestId('password-field'))
      .find('input')
      .setValue(password)
  }

  describe("Form validation", () => {
    describe("Email field", () => {
      it.each([
        { value: '', message: 'Veuillez entrer un e-mail' },
        { value: null, message: 'Veuillez entrer un e-mail' },
        { value: 'hey', message: "L'e-mail doit être valide" },
        { value: 'hello@', message: "L'e-mail doit être valide" },
        { value: '@example.fr', message: "L'e-mail doit être valide" },
        { value: 'hello@example', message: "L'e-mail doit être valide" },
        { value: 'hello@.fr', message: "L'e-mail doit être valide" },
      ])('should show $message when the value is $value', async ({ value, message }) => {
        wrapper = mountComponent()

        await setFieldValues(value)
        await updateFormAfterDataSet()

        const field = wrapper
          .get(getByTestId('email-field'))
          .find('.v-messages__message')

        expect(field.exists()).toBe(true)
        expect(field.text()).toBe(message)
      })

      it.each([
        { value: 'test@example.com' },
        { value: 'a@example.com' },
        { value: 'john.doe@gmail.com' },
      ])('should be valid with $value', async ({ value }) => {
        wrapper = mountComponent()

        await setFieldValues('email@example.com', value)
        await updateFormAfterDataSet()

        const field = wrapper
          .get(getByTestId('email-field'))
          .find('.v-messages__message')

        expect(field.exists()).toBe(false)
      })
    })

    describe("Password field", () => {
      it.each([
        { value: '', message: 'Veuillez entrer un mot de passe' },
        { value: null, message: 'Veuillez entrer un mot de passe' },
      ])('should show $message when the value is $value', async ({ value, message }) => {
        wrapper = mountComponent()

        await setFieldValues('email@example.com', value)
        await updateFormAfterDataSet()

        const field = wrapper
          .get(getByTestId('password-field'))
          .find('.v-messages__message')

        expect(field.exists()).toBe(true)
        expect(field.text()).toBe(message)
      })

      it.each([
        { value: 'password' },
        { value: '1234' },
        { value: 'password1234' },
        { value: 'a' },
        { value: 'mdp' },
      ])('should be valid with $value', async ({ value }) => {
        wrapper = mountComponent()

        await setFieldValues('email@example.com', value)
        await updateFormAfterDataSet()

        const field = wrapper
          .get(getByTestId('password-field'))
          .find('.v-messages__message')

        expect(field.exists()).toBe(false)
      })
    })
  })
})
