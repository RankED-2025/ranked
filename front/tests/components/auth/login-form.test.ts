import { afterEach, describe, vi, beforeEach, it, expect, MockedFunction } from 'vitest'
import { flushPromises, mount, VueWrapper } from '@vue/test-utils'
import { getByTestId, globalTestPlugins } from '../../util/vuetify-utils'
import LoginForm from '../../../src/components/auth/LoginForm.vue'
import { useUserStore } from '../../../src/stores/userStore'
import router from '../../../src/router'
import { VAlert, VForm } from 'vuetify/components'
import { nextTick } from 'vue'
import { defaultStatusMessageCases } from '../../util/status-messages'
import { expectFieldValidationMessage } from '../../util/form-assertions'

vi.mock('../../../src/stores/userStore')
vi.mock('../../../src/router', () => ({ default: { push: vi.fn() } }))

const mountComponent = (): VueWrapper => {
  return mount(LoginForm, {
    global: {
      plugins: globalTestPlugins
    }
  })
}

type UserStore = ReturnType<typeof useUserStore>

// ------------------------------------------------------------------

describe("LoginForm Component", () => {
  let wrapper: VueWrapper
  let loginAttemptSpy: MockedFunction<UserStore['loginAttempt']>

  beforeEach(() => {
    loginAttemptSpy = vi.fn().mockResolvedValue(true)

    const mockStore = {
      loginAttempt: loginAttemptSpy,
    } satisfies Partial<UserStore>

    vi.mocked(useUserStore).mockReturnValue(mockStore as unknown as UserStore)
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

        expectFieldValidationMessage(wrapper, 'email-field', message)
      })

      it.each([
        { value: 'test@example.com' },
        { value: 'a@example.com' },
        { value: 'john.doe@gmail.com' },
      ])('should be valid with $value', async ({ value }) => {
        wrapper = mountComponent()

        await setFieldValues('email@example.com', value)
        await updateFormAfterDataSet()

        expectFieldValidationMessage(wrapper, 'email-field', null)
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

        expectFieldValidationMessage(wrapper, 'password-field', message)
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

        expectFieldValidationMessage(wrapper, 'password-field', null)
      })
    })
  })

  describe("Password visibility toggle", () => {
    it('should toggle the password field type when the append icon is clicked', async () => {
      wrapper = mountComponent()

      const input = wrapper.get(getByTestId('password-field')).find('input')
      const toggleBtn = wrapper.find('[aria-label="Mot de passe appended action"]')

      expect(input.attributes('type')).toBe('password')

      await toggleBtn.trigger('click')
      await nextTick()
      expect(input.attributes('type')).toBe('text')

      await toggleBtn.trigger('click')
      await nextTick()
      expect(input.attributes('type')).toBe('password')
    })
  })

  describe("Form submission", () => {
    const validEmail = 'test@example.com'
    const validPassword = 'password123'

    describe("when the form is invalid", () => {
      it('should not call loginAttempt', async () => {
        wrapper = mountComponent()

        await wrapper.get(getByTestId('target-form')).trigger('submit')
        await flushPromises()

        expect(loginAttemptSpy).not.toHaveBeenCalled()
      })
    })

    describe("when the form is valid", () => {
      beforeEach(async () => {
        wrapper = mountComponent()
        await setFieldValues(validEmail, validPassword)
        await updateFormAfterDataSet()
      })

      it('should call loginAttempt with the correct credentials', async () => {
        await wrapper.get(getByTestId('target-form')).trigger('submit')
        await flushPromises()

        expect(loginAttemptSpy).toHaveBeenCalledWith({ email: validEmail, password: validPassword })
      })

      it('should redirect to home on successful login', async () => {
        await wrapper.get(getByTestId('target-form')).trigger('submit')
        await flushPromises()

        expect(router.push).toHaveBeenCalledWith('/')
      })

      it('should not show an error message on successful login', async () => {
        await wrapper.get(getByTestId('target-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.find(getByTestId('error-message')).exists()).toBe(false)
      })

      it('should show the generic fallback message when the error has no HTTP status', async () => {
        loginAttemptSpy.mockRejectedValue({})

        await wrapper.get(getByTestId('target-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.get(getByTestId('error-message')).text())
          .toBe('Une erreur est survenue. Veuillez réessayer.')
      })

      // LOGIN_STATUS_OVERRIDES only overrides 401 — every other status must fall back to
      // the shared DEFAULT_STATUS_MESSAGES map, so this is generated from it directly.
      describe.each(
        defaultStatusMessageCases([401])
      )('when the server responds with status $status', ({ status, message, type }) => {
        it(`shows the default "${type}" message`, async () => {
          loginAttemptSpy.mockRejectedValue({ response: { status } })

          await wrapper.get(getByTestId('target-form')).trigger('submit')
          await flushPromises()

          const alert = wrapper.get(getByTestId('error-message'))
          expect(alert.text()).toBe(message)
          expect(wrapper.findComponent(VAlert).props('type')).toBe(type)
        })
      })

      // Page-specific overrides declared in LOGIN_STATUS_OVERRIDES.
      describe.each([
        { status: 401, message: 'Email ou mot de passe incorrect. Veuillez réessayer.', type: 'error' },
      ])('when the server responds with overridden status $status', ({ status, message, type }) => {
        it(`shows the overridden "${type}" message`, async () => {
          loginAttemptSpy.mockRejectedValue({ response: { status } })

          await wrapper.get(getByTestId('target-form')).trigger('submit')
          await flushPromises()

          const alert = wrapper.get(getByTestId('error-message'))
          expect(alert.text()).toBe(message)
          expect(wrapper.findComponent(VAlert).props('type')).toBe(type)
        })
      })

      it('should not redirect when loginAttempt throws', async () => {
        loginAttemptSpy.mockRejectedValue(new Error('invalid credentials'))

        await wrapper.get(getByTestId('target-form')).trigger('submit')
        await flushPromises()

        expect(router.push).not.toHaveBeenCalled()
      })
    })
  })
})
