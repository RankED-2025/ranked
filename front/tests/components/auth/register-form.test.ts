import { mount, VueWrapper } from '@vue/test-utils'
import { globalTestPlugins, getByTestId } from '../../util/vuetify-utils'
import RegisterForm from '../../../src/components/auth/RegisterForm.vue'
import { afterEach, beforeEach, describe, vi, it, MockInstance, expect } from 'vitest'
import { useUserStore } from '../../../src/stores/userStore'
import { createPinia, setActivePinia } from 'pinia'
import { VAlert, VForm } from 'vuetify/components'
import { nextTick } from 'vue'
import { flushPromises } from '@vue/test-utils'
import router from '../../../src/router'
import { defaultStatusMessageCases } from '../../util/status-messages'
import { expectFieldValidationMessage } from '../../util/form-assertions'

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
    registerSpy = vi.spyOn(userStore, 'registerAttempt').mockResolvedValue(undefined)
    loginSpy = vi.spyOn(userStore, 'loginAttempt').mockResolvedValue(undefined)
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
      ])("shows $message for value $value", async ({ value, message }) => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('name-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        expectFieldValidationMessage(wrapper, 'name-field', message)
      })
    })

    describe("Firstname field", () => {
      it.each([
        { value: '', message: "Veuillez entrer un nom d'utilisateur" },
        { value: 'XY', message: "Le nom d'utilisateur doit contenir au moins 3 caractères" },
        { value: 'XYZ', message: null },
      ])("shows $message for value $value", async ({ value, message }) => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('firstname-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        expectFieldValidationMessage(wrapper, 'firstname-field', message)
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
      ])("shows $message for value $value", async ({ value, message }) => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('email-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        expectFieldValidationMessage(wrapper, 'email-field', message)
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
      ])("shows $message for value $value", async ({ value, message }) => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('password-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        expectFieldValidationMessage(wrapper, 'password-field', message)
      })
    })

    describe("Confirm Password field", () => {
      it("shows error when passwords do not match", async () => {
        wrapper = mountComponent()

        await setFormData({ password: 'ValidPass123!', confirmPassword: 'DifferentPass123!' })
        await updateFormAfterDataSet()

        expectFieldValidationMessage(wrapper, 'confirm-password-field', 'Les mots de passe ne correspondent pas')
      })

      it("shows no error when passwords match", async () => {
        wrapper = mountComponent()

        await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })
        await updateFormAfterDataSet()

        expectFieldValidationMessage(wrapper, 'confirm-password-field', null)
      })

      it("shows required error when empty", async () => {
        wrapper = mountComponent()

        await setFormData({ password: 'ValidPass123!', confirmPassword: '' })
        await updateFormAfterDataSet()

        expectFieldValidationMessage(wrapper, 'confirm-password-field', 'La confirmation du mot de passe est requise')
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
        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        expect(registerSpy).toHaveBeenCalledWith({
          name: 'John',
          firstname: 'Doe',
          email: 'john.doe@example.com',
          password: 'ValidPass123!'
        })
      })

      it("shows success message", async () => {
        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        const successAlert = wrapper.get(getByTestId('success-alert'))
        expect(successAlert.text()).toBe('Inscription réussie ! Connexion en cours...')
      })

      it("attempts login after delay", async () => {
        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        vi.advanceTimersByTime(2000)
        await flushPromises()

        expect(loginSpy).toHaveBeenCalledWith({
          email: 'john.doe@example.com',
          password: 'ValidPass123!'
        })
      })

      it("redirects to home on successful login", async () => {
        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        vi.advanceTimersByTime(2000)
        await flushPromises()

        expect(routerPushSpy).toHaveBeenCalledWith('/')
      })

      it("redirects to login on failed login", async () => {
        loginSpy.mockRejectedValue(new Error('invalid credentials'))

        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        vi.advanceTimersByTime(2000)
        await flushPromises()

        expect(routerPushSpy).toHaveBeenCalledWith('/login')
      })
    })

    describe("failed registration", () => {
      const submitWithFormData = async () => {
        wrapper = mountComponent()
        await setFormData({
          name: 'John',
          firstname: 'Doe',
          email: 'john.doe@example.com',
          password: 'ValidPass123!',
          confirmPassword: 'ValidPass123!'
        })

        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()
      }

      // REGISTER_STATUS_OVERRIDES only overrides 409 — every other status must fall back to
      // the shared DEFAULT_STATUS_MESSAGES map, so this is generated from it directly.
      describe.each(
        defaultStatusMessageCases([409])
      )('when the server responds with status $status', ({ status, message, type }) => {
        it(`shows the default "${type}" message`, async () => {
          registerSpy.mockRejectedValue({ response: { status } })
          await submitWithFormData()

          const errorAlert = wrapper.get(getByTestId('error-alert'))
          expect(errorAlert.text()).toBe(message)
          expect(wrapper.findComponent(VAlert).props('type')).toBe(type)
        })
      })

      // Page-specific overrides declared in REGISTER_STATUS_OVERRIDES.
      describe.each([
        { status: 409, message: 'Un compte existe déjà avec cette adresse email.', type: 'error' },
      ])('when the server responds with overridden status $status', ({ status, message, type }) => {
        it(`shows the overridden "${type}" message`, async () => {
          registerSpy.mockRejectedValue({ response: { status, data: { error: 'email already used' } } })
          await submitWithFormData()

          const errorAlert = wrapper.get(getByTestId('error-alert'))
          expect(errorAlert.text()).toBe(message)
          expect(wrapper.findComponent(VAlert).props('type')).toBe(type)
        })
      })

      it("shows fallback error message when registration fails without HTTP status", async () => {
        registerSpy.mockRejectedValue(new Error())

        wrapper = mountComponent()
        await setFormData({
          name: 'John',
          firstname: 'Doe',
          email: 'john.doe@example.com',
          password: 'ValidPass123!',
          confirmPassword: 'ValidPass123!'
        })

        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        const errorAlert = wrapper.get(getByTestId('error-alert'))
        expect(errorAlert.text()).toBe('Une erreur est survenue. Veuillez réessayer.')
      })

      it("does not call registerAttempt when form is invalid", async () => {
        wrapper = mountComponent()

        // Form is invalid by default, so submit should not call registerAttempt
        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        expect(registerSpy).not.toHaveBeenCalled()
      })

      it("does not show messages when form is invalid on submit", async () => {
        wrapper = mountComponent()

        // Form is invalid, submit should be prevented
        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        const successAlert = wrapper.find(getByTestId('success-alert'))
        const errorAlert = wrapper.find(getByTestId('error-alert'))

        expect(successAlert.exists()).toBe(false)
        expect(errorAlert.exists()).toBe(false)
      })

      it("does not navigate when form is invalid on submit", async () => {
        wrapper = mountComponent()

        // Form is invalid, submit should be prevented
        await wrapper.get(getByTestId('register-form')).trigger('submit')
        await flushPromises()

        expect(routerPushSpy).not.toHaveBeenCalled()
        expect(loginSpy).not.toHaveBeenCalled()
      })
    })
  })

  describe("Interactions", () => {
    it('Should toggle the password visibility when the toggle button is clicked', async () => {
      wrapper = mountComponent()

      const passwordField = wrapper.get(getByTestId('password-field'))
      const passwordInput = passwordField.find('input')
      const toggleButton = wrapper.find('[aria-label="Mot de passe appended action"]')

      // Initially, the password should be hidden
      expect(passwordInput.attributes('type')).toBe('password')

      // Emit the click:append-inner event to show the password
      await toggleButton.trigger('click')
      await nextTick()
      expect(passwordInput.attributes('type')).toBe('text')

      // Emit again to hide the password
      await toggleButton.trigger('click')
      await nextTick()
      expect(passwordInput.attributes('type')).toBe('password')
    })
  })

  describe("Form password watch", () => {

    it("revalidates confirm password when password changes", async () => {
      wrapper = mountComponent()

      await setFormData({
        password: 'ValidPass123!',
        confirmPassword: 'ValidPass123!',
      })

      // Assert no error initially
      const errorElementBeforeChange = wrapper
        .get(getByTestId('confirm-password-field'))
        .find('.v-messages__message')

      expect(errorElementBeforeChange.exists()).toBe(false)

      // Change the password to something that doesn't match confirm password
      await setFormData({
        password: 'DifferentPass123!',
      })

      const errorElement = wrapper
        .get(getByTestId('confirm-password-field'))
        .find('.v-messages__message')

      expect(errorElement.exists()).toBe(true)
      expect(errorElement.text()).toBe('Les mots de passe ne correspondent pas')
    })

    it("calls validate on confirm password field when password changes", async () => {
      wrapper = mountComponent()

      const validateSpy = vi.fn()
      wrapper.vm.confirmPasswordFieldRef = { validate: validateSpy }

      await setFormData({ password: 'ValidPass123!' })

      // Change password to trigger watch
      await wrapper.get(getByTestId('password-field')).find('input').setValue('NewPass123!')
      await nextTick()

      expect(validateSpy).toHaveBeenCalled()
    })

    it("does not call validate if confirm password field ref does not exist", async () => {
      wrapper = mountComponent()

      // Ensure the ref doesn't have a validate method
      wrapper.vm.$refs.confirmPasswordFieldRef = null

      // Change password to trigger watch
      await wrapper.get(getByTestId('password-field')).find('input').setValue('NewPass123!')
      await nextTick()

      // Should not throw any error
      expect(wrapper.exists()).toBe(true)
    })
  })
})
