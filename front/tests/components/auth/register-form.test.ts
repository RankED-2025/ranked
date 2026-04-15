import { mount, VueWrapper } from '@vue/test-utils'
import { globalTestPlugins, getByTestId } from '../../util/vuetify-utils'
import RegisterForm from '../../../src/components/auth/RegisterForm.vue'
import { afterEach, beforeEach, describe, vi, it, MockInstance, expect } from 'vitest'
import { useUserStore } from '../../../src/stores/userStore'
import { createPinia, setActivePinia } from 'pinia'
import { VForm } from 'vuetify/components'
import { nextTick } from 'vue'
import { flushPromises } from '@vue/test-utils'
import router from '../../../src/router'

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
  let registerSpy: MockInstance<typeof userStore.registerAttempt>;
  let loginSpy: MockInstance<typeof userStore.loginAttempt>;
  let routerPushSpy: MockInstance<typeof router.push>;

  beforeEach(() => {
    vi.useFakeTimers()
    setActivePinia(createPinia())
    userStore = useUserStore()
    registerSpy = vi.spyOn(userStore, 'registerAttempt').mockResolvedValue(true)
    loginSpy = vi.spyOn(userStore, 'loginAttempt').mockResolvedValue(true)
    routerPushSpy = vi.spyOn(router, 'push').mockImplementation(() => {})
  })

  afterEach(() => {
    vi.clearAllMocks()
    vi.restoreAllMocks()
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

  /**
   * Sets all form fields with provided data
   */
  const setFormData = async (data: {
    name?: string
    firstname?: string
    email?: string
    password?: string
    confirmPassword?: string
  }) => {
    const dataKeys = {
      name: 'name-field',
      firstname: 'firstname-field',
      email: 'email-field',
      password: 'password-field',
      confirmPassword: 'confirm-password-field'
    }

    for (const [key, testId] of Object.entries(dataKeys)) {
      if (data[key as keyof typeof dataKeys] !== undefined) {
        await wrapper.get(getByTestId(testId)).find('input').setValue(data[key as keyof typeof dataKeys]!)
      }
    }

    await updateFormAfterDataSet()
  }

  describe("Form validation", () => {
    describe("Name field", () => {
      it.each([
        { value: '', message: "Veuillez entrer un nom d'utilisateur" },
        { value: null, message: "Veuillez entrer un nom d'utilisateur" },
        { value: 'AB', message: "Le nom d'utilisateur doit contenir au moins 3 caractères" },
        { value: 'ABC', message: null },
        { value: 'ABCD', message: null },
      ])("shows '$message' for value '$value'", async ({ value, message }) => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('name-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        const errorElement = wrapper
          .get(getByTestId('name-field'))
          .find('.v-messages__message')

        if (message) {
          expect(errorElement.exists()).toBe(true)
          expect(errorElement.text()).toBe(message)
        } else {
          expect(errorElement.exists()).toBe(false)
        }
      })
    })

    describe("Firstname field", () => {
      it.each([
        { value: '', message: "Veuillez entrer un nom d'utilisateur" },
        { value: 'XY', message: "Le nom d'utilisateur doit contenir au moins 3 caractères" },
        { value: 'XYZ', message: null },
      ])("shows '$message' for value '$value'", async ({ value, message }) => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('firstname-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        const errorElement = wrapper
          .get(getByTestId('firstname-field'))
          .find('.v-messages__message')

        if (message) {
          expect(errorElement.exists()).toBe(true)
          expect(errorElement.text()).toBe(message)
        } else {
          expect(errorElement.exists()).toBe(false)
        }
      })
    })

    describe("Email field", () => {
      it.each([
        { value: '', message: 'Veuillez entrer un e-mail' },
        { value: 'invalid', message: "L'e-mail doit être valide" },
        { value: 'test@', message: "L'e-mail doit être valide" },
        { value: '@example.com', message: "L'e-mail doit être valide" },
        { value: 'test@example.com', message: null },
        { value: 'user.name+tag@example.co.uk', message: null },
      ])("shows '$message' for value '$value'", async ({ value, message }) => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('email-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        const errorElement = wrapper
          .get(getByTestId('email-field'))
          .find('.v-messages__message')

        if (message) {
          expect(errorElement.exists()).toBe(true)
          expect(errorElement.text()).toBe(message)
        } else {
          expect(errorElement.exists()).toBe(false)
        }
      })
    })

    describe("Password field", () => {
      it.each([
        { value: '', message: 'Veuillez entrer un mot de passe' },
        { value: 'short', message: 'Le mot de passe doit contenir au moins 8 caractères' },
        { value: 'nouppercase123!', message: 'Le mot de passe doit contenir au moins une majuscule' },
        { value: 'NOLOWERCASE123!', message: 'Le mot de passe doit contenir au moins une minuscule' },
        { value: 'NoNumber!', message: 'Le mot de passe doit contenir au moins un chiffre' },
        { value: 'NoSpecial123', message: 'Le mot de passe doit contenir un caractère spécial (@, $, !, %, *, ?, &)' },
        { value: 'ValidPass123!', message: null },
      ])("shows '$message' for value '$value'", async ({ value, message }) => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('password-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        const errorElement = wrapper
          .get(getByTestId('password-field'))
          .find('.v-messages__message')

        if (message) {
          expect(errorElement.exists()).toBe(true)
          expect(errorElement.text()).toBe(message)
        } else {
          expect(errorElement.exists()).toBe(false)
        }
      })
    })

    describe("Confirm Password field", () => {
      it("shows error when passwords do not match", async () => {
        wrapper = mountComponent()

        await setFormData({ password: 'ValidPass123!', confirmPassword: 'DifferentPass123!' })
        await updateFormAfterDataSet()

        const errorElement = wrapper
          .get(getByTestId('confirm-password-field'))
          .find('.v-messages__message')

        expect(errorElement.exists()).toBe(true)
        expect(errorElement.text()).toBe('Les mots de passe ne correspondent pas')
      })

      it("shows no error when passwords match", async () => {
        wrapper = mountComponent()

        await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })
        await updateFormAfterDataSet()

        const errorElement = wrapper
          .get(getByTestId('confirm-password-field'))
          .find('.v-messages__message')

        expect(errorElement.exists()).toBe(false)
      })

      it("shows required error when empty", async () => {
        wrapper = mountComponent()

        await setFormData({ password: 'ValidPass123!', confirmPassword: '' })
        await updateFormAfterDataSet()

        const errorElement = wrapper
          .get(getByTestId('confirm-password-field'))
          .find('.v-messages__message')

        expect(errorElement.exists()).toBe(true)
        expect(errorElement.text()).toBe('La confirmation du mot de passe est requise')
      })
    })
  })

  describe("Form submission", () => {
    it("disables submit button when form is invalid", async () => {
      wrapper = mountComponent()

      const submitButton = wrapper.get(getByTestId('submit-button'))
      expect(submitButton.attributes('disabled')).toBeDefined()
    })

    it("enables submit button when form is valid", async () => {
      wrapper = mountComponent()

      await setFormData({
        name: 'John',
        firstname: 'Doe',
        email: 'john.doe@example.com',
        password: 'ValidPass123!',
        confirmPassword: 'ValidPass123!'
      })

      const submitButton = wrapper.get(getByTestId('submit-button'))
      expect(submitButton.attributes('disabled')).toBeUndefined()
    })

    describe("successful registration", () => {
      beforeEach(async () => {
        wrapper = mountComponent()
        await setFormData({
          name: 'John',
          firstname: 'Doe',
          email: 'john.doe@example.com',
          password: 'ValidPass123!',
          confirmPassword: 'ValidPass123!'
        })
      })

      it("calls registerAttempt with correct data", async () => {
        await wrapper.get(getByTestId('submit-button')).trigger('click')
        await flushPromises()

        expect(registerSpy).toHaveBeenCalledWith({
          name: 'John',
          firstname: 'Doe',
          email: 'john.doe@example.com',
          password: 'ValidPass123!'
        }, 'eleve')
      })

      it("shows success message", async () => {
        await wrapper.get(getByTestId('submit-button')).trigger('click')
        await flushPromises()

        const successAlert = wrapper.get(getByTestId('success-alert'))
        expect(successAlert.text()).toBe('Inscription réussie! Connexion en cours...')
      })

      it("attempts login after delay", async () => {
        await wrapper.get(getByTestId('submit-button')).trigger('click')
        await flushPromises()

        vi.advanceTimersByTime(2000)
        await flushPromises()

        expect(loginSpy).toHaveBeenCalledWith({
          email: 'john.doe@example.com',
          password: 'ValidPass123!'
        })
      })

      it("redirects to home on successful login", async () => {
        await wrapper.get(getByTestId('submit-button')).trigger('click')
        await flushPromises()

        vi.advanceTimersByTime(2000)
        await flushPromises()

        expect(routerPushSpy).toHaveBeenCalledWith('/')
      })

      it("redirects to login on failed login", async () => {
        loginSpy.mockResolvedValue(false)

        await wrapper.get(getByTestId('submit-button')).trigger('click')
        await flushPromises()

        vi.advanceTimersByTime(2000)
        await flushPromises()

        expect(routerPushSpy).toHaveBeenCalledWith('/login')
      })
    })

    describe("failed registration", () => {
      it("shows error message", async () => {
        registerSpy.mockResolvedValue(false)

        wrapper = mountComponent()
        await setFormData({
          name: 'John',
          firstname: 'Doe',
          email: 'john.doe@example.com',
          password: 'ValidPass123!',
          confirmPassword: 'ValidPass123!'
        })

        await wrapper.get(getByTestId('submit-button')).trigger('click')
        await flushPromises()

        const errorAlert = wrapper.get(getByTestId('error-alert'))
        expect(errorAlert.text()).toBe('Une erreur est survenue lors de l\'inscription. Veuillez réessayer.')
      })
    })
  })
})
