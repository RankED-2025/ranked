import { afterEach, describe, vi, beforeEach, it, expect } from 'vitest'
import { flushPromises, mount, VueWrapper } from '@vue/test-utils'
import { getByTestId, globalTestPlugins } from '../../util/vuetify-utils'
import LoginForm from '../../../src/components/auth/LoginForm.vue'
import { useUserStore } from '../../../src/stores/userStore'
import router from '../../../src/router'
import { VForm } from 'vuetify/components'
import { nextTick } from 'vue'

vi.mock('../../../src/stores/userStore')
vi.mock('../../../src/router', () => ({ default: { push: vi.fn() } }))

const mountComponent = (): VueWrapper => {
  return mount(LoginForm, {
    global: {
      plugins: globalTestPlugins
    }
  })
}

// ------------------------------------------------------------------

describe("LoginForm Component", () => {
  let wrapper: VueWrapper
  let loginAttemptSpy: ReturnType<typeof vi.fn>

  beforeEach(() => {
    loginAttemptSpy = vi.fn().mockResolvedValue(true)
    vi.mocked(useUserStore).mockReturnValue({ loginAttempt: loginAttemptSpy } as any)
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

      it('should show an error message when loginAttempt returns false', async () => {
        loginAttemptSpy.mockResolvedValue(false)

        await wrapper.get(getByTestId('target-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.get(getByTestId('error-message')).text())
          .toBe('Email ou mot de passe incorrect. Veuillez réessayer.')
      })

      it('should not redirect when loginAttempt returns false', async () => {
        loginAttemptSpy.mockResolvedValue(false)

        await wrapper.get(getByTestId('target-form')).trigger('submit')
        await flushPromises()

        expect(router.push).not.toHaveBeenCalled()
      })
    })
  })
})
