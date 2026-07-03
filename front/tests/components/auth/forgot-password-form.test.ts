import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import ForgotPasswordForm from '../../../src/components/auth/ForgotPasswordForm.vue'
import { getByTestId, globalTestPlugins, testRouter, validateVuetifyForm } from '../../util/vuetify-utils'
import { VAlert } from 'vuetify/components'
import { passwordResetService } from '../../../src/services/passwordResetService'
import { defaultStatusMessageCases } from '../../util/status-messages'
import { expectFieldValidationMessage } from '../../util/form-assertions'

vi.mock('../../../src/services/passwordResetService', () => ({
  passwordResetService: {
    requestReset: vi.fn(),
    confirmReset: vi.fn()
  }
}))

const mountComponent = (): VueWrapper => {
  return mount(ForgotPasswordForm, {
    global: {
      plugins: globalTestPlugins
    }
  })
}

// --------------------------------------------------------------------------------------------

describe('ForgotPasswordForm component', () => {
  let wrapper: VueWrapper;

  const updateFormAfterDataSet = () => validateVuetifyForm(wrapper, 'formRef')

  /**
   * Set all the forms fields to a said value
   */
  const setFieldValues = async (data = {
    email: 'email@example.com'
  }) => {
    await wrapper
      .get(getByTestId('email-field'))
      .find('input')
      .setValue(data.email)
  }

  const submitForm = async () => {
    await wrapper
      .get(getByTestId('target-form'))
      .trigger('submit')
  }

  afterEach(() => {
    vi.clearAllMocks()
    wrapper?.unmount()
  })

  describe("Rendering", () => {
    describe("card title", () => {
      it('should render with the expected text', () => {
        wrapper = mountComponent()

        expect(wrapper.findComponent(getByTestId('form-title')).text())
          .toBe('Mot de passe oublié')
      })
    })

    describe('card subtitle', () => {
      it('should render with the expected text', () => {
        wrapper = mountComponent()

        expect(wrapper.findComponent(getByTestId('form-subtitle')).text())
          .toBe('Entrez votre adresse email pour recevoir un lien de réinitialisation')
      })
    })

    describe('email field', () => {
      it('should render correctly', () => {
        wrapper = mountComponent()

        expect(wrapper.find(getByTestId('email-field')).exists())
          .toBe(true)
      })

      it('should be empty by default', () => {
        wrapper = mountComponent()

        const element: HTMLInputElement = wrapper
          .get(getByTestId('email-field'))
          .find('input')
          .element as HTMLInputElement

        expect(element.value).toBe('')
      })
    })

    describe("success message", () => {
      it('should not render when the component mounts', () => {
        wrapper = mountComponent()

        expect(wrapper.find(getByTestId('success-message')).exists())
          .toBe(false)
      })
    })

    describe("error message", () => {
      it('should not render when the component mounts', () => {
        wrapper = mountComponent()

        expect(wrapper.find(getByTestId('error-message')).exists())
          .toBe(false)
      })
    })
  })

  describe('Form validation', () => {
    describe("Submit button", () => {

      it.each([
        { value: '', },
        { value: null, },
        { value: undefined, },
      ])('should remain disabled when the email field is $value', async ({ value }) => {
        wrapper = mountComponent()

        // set the email v-model to an empty value
        await wrapper
          .get(getByTestId('email-field'))
          .find('input')
          .setValue(value)

        await updateFormAfterDataSet()

        const vButton = wrapper.find(getByTestId('submit-button'))

        expect(vButton.attributes('disabled')).toBeDefined()
        expect(vButton.classes()).toContain('v-btn--disabled')
      })

      it('should remain disabled when the email is invalid', async () => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('email-field'))
          .find('input')
          .setValue('not_an_email')

        await updateFormAfterDataSet()

        const vButton = wrapper.find(getByTestId('submit-button'))

        expect(vButton.attributes('disabled')).toBeDefined()
        expect(vButton.classes()).toContain('v-btn--disabled')
      })

      it('should become enabled when a valid email is entered', async () => {
        wrapper = mountComponent()

        await wrapper
          .get(getByTestId('email-field'))
          .find('input')
          .setValue('test@example.fr')

        await updateFormAfterDataSet()

        const vButton = wrapper.find(getByTestId('submit-button'))

        expect(vButton.attributes('disabled')).toBeUndefined()
        expect(vButton.classes()).not.toContain('v-btn--disabled')
      })

      it('should not call the requestRequest when the form is not valid', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'not_an_email!' })
        await submitForm()
        await updateFormAfterDataSet()

        expect(passwordResetService.requestReset).not.toHaveBeenCalled()
      })
    })

    describe('Form rules', () => {
      describe('Email field', () => {

        it.each([
          { value: '', message: 'Veuillez entrer un e-mail' },
          { value: null, message: 'Veuillez entrer un e-mail' },
          { value: 'hey', message: "L'e-mail doit être valide" },
          { value: 'hello@', message: "L'e-mail doit être valide" },
          { value: '@example.fr', message: "L'e-mail doit être valide" },
          { value: 'hello@example', message: "L'e-mail doit être valide" },
          { value: 'hello@.fr', message: "L'e-mail doit être valide" },
        ])('validation should not pass with message $message when $value has been set in the field', async ({ value, message }) => {
          wrapper = mountComponent()

          await wrapper
            .get(getByTestId('email-field'))
            .find('input')
            .setValue(value)

          await updateFormAfterDataSet()

          expectFieldValidationMessage(wrapper, 'email-field', message)
        })
      })
    })


    describe('Valid form', () => {
      it('should call "passwordResetService.requestReset" with the correct payload when the form is valid', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'john@example.com' })
        await submitForm()

        expect(passwordResetService.requestReset)
          .toHaveBeenCalledExactlyOnceWith({ email: 'john@example.com' })
      })

      it('should show the success message returned by the password reset service', async () => {
        vi.mocked(passwordResetService.requestReset).mockResolvedValue({
          message: 'Un email a été envoyé'
        })

        wrapper = mountComponent()

        await setFieldValues({ email: 'john@example.com' })
        await submitForm()

        const successMessage = wrapper.find(getByTestId('success-message'))

        expect(successMessage.exists()).toBe(true)
        expect(successMessage.text()).toBe('Un email a été envoyé')
      })

      it('should clear the email field when the form submits', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'john@example.com' })
        await submitForm()
        await updateFormAfterDataSet()

        expect(wrapper.vm.email)
          .toBe('')

        expect(wrapper.get(getByTestId('email-field')).find('input').element.value)
          .toBe('')
      })

      it('should not show the error element when the reset request is successful', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'john@example.com' })
        await submitForm()
        await updateFormAfterDataSet()

        expect(wrapper.find(getByTestId('error-message')).exists())
          .toBe(false)

        expect(wrapper.find(getByTestId('success-message')).exists())
          .toBe(true)
      })
    })
  })

  describe('Error in the submitting process', () => {
    // ForgotPasswordForm declares no page-specific overrides, so every status must show
    // the shared DEFAULT_STATUS_MESSAGES message — generated from it directly.
    describe.each(
      defaultStatusMessageCases()
    )('when the server responds with status $status', ({ status, message, type }) => {
      it(`displays the default "${type}" message`, async () => {
        wrapper = mountComponent()

        vi.mocked(passwordResetService.requestReset)
          .mockRejectedValue({ response: { status } })

        await setFieldValues({ email: 'john@example.com' })
        await submitForm()
        await updateFormAfterDataSet()

        const errorMessage = wrapper.get(getByTestId('error-message'))
        expect(errorMessage.text()).toBe(message)
        expect(wrapper.findComponent(VAlert).props('type')).toBe(type)
      })
    })

    it('should display the generic fallback message when no HTTP status is provided on the thrown error', async () => {
      wrapper = mountComponent()

      vi.mocked(passwordResetService.requestReset)
        .mockThrow(new Error(undefined))

      await setFieldValues({ email: 'john@example.com' })
      await submitForm()
      await updateFormAfterDataSet()

      const errorMessage = wrapper.find(getByTestId('error-message'))

      expect(errorMessage.exists()).toBe(true)
      expect(errorMessage.text()).toBe('Une erreur est survenue. Veuillez réessayer.')
    })

    it('should not display the success message when an error is thrown', async () => {
      wrapper = mountComponent()

      vi.mocked(passwordResetService.requestReset)
        .mockThrow(new Error(undefined))

      await setFieldValues({ email: 'john@example.com' })
      await submitForm()
      await updateFormAfterDataSet()

      expect(wrapper.find(getByTestId('success-message')).exists())
        .toBe(false)
    })

    it('should not clear the email field when an error is thrown', async () => {
      wrapper = mountComponent()

      vi.mocked(passwordResetService.requestReset)
        .mockRejectedValue({ response: { status: 500 } })

      await setFieldValues({ email: 'john@example.com' })
      await submitForm()
      await updateFormAfterDataSet()

      expect(wrapper.get(getByTestId('email-field')).find('input').element.value)
        .toBe('john@example.com')

      expect(wrapper.vm.email).toBe('john@example.com')
    })
  })

  describe("Loading state", () => {
    let resolve!: (value: unknown) => void

    beforeEach(() => {
      const pendingPromise = new Promise((res) => { resolve = res })
      vi.mocked(passwordResetService.requestReset).mockReturnValue(pendingPromise)
    })

    afterEach(() => {
      resolve({ message: 'ok' })
    })

    describe("Submit button", () => {
      it('should show a loading indicator', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'test@example.com' })
        await submitForm()
        await updateFormAfterDataSet()

        expect(wrapper.get(getByTestId('submit-button')).classes())
          .toContain('v-btn--loading')
      })

      it('should be disabled when the form is valid', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'test@example.com' })
        await submitForm()
        await updateFormAfterDataSet()

        expect(wrapper.vm.valid).toBe(true)

        expect(wrapper.get(getByTestId('submit-button')).classes())
          .toContain('v-btn--disabled')
      })

      it('should be disabled when the form is not valid', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'not_valid_email' })
        await submitForm()
        await updateFormAfterDataSet()

        expect(wrapper.vm.valid).toBe(false)

        expect(wrapper.get(getByTestId('submit-button')).classes())
          .toContain('v-btn--disabled')
      })
    })

    describe('Email field', () => {
      it('should be disabled', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'test@example.com' })
        await submitForm()

        const input = wrapper
          .get(getByTestId('email-field'))
          .find('input')
          .element as HTMLInputElement

        expect(input.disabled).toBe(true)
      })
    })

    describe("Back to login button", () => {
      it('should be disabled', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: 'test@example.com' })
        await submitForm()

        expect(wrapper.get(getByTestId('go-back-button')).classes())
          .toContain('v-btn--disabled')
      })
    })

    describe("Routing", () => {
      it('should not be triggered when the button is loading', async () => {
        const pushRouteSpy = vi.spyOn(testRouter, 'push')

        wrapper = mountComponent()
        await testRouter.isReady()

        await setFieldValues({ email: 'test@example.com' })
        await submitForm()

        await wrapper.find(getByTestId('go-back-button')).trigger('click')

        expect(pushRouteSpy).not.toHaveBeenCalled()
      })
    })

    describe("Error and success messages", () => {
      it('should be cleared after each submission attempt', async () => {
        vi.mocked(passwordResetService.requestReset)

        wrapper = mountComponent()

        // set an error and a success message
        wrapper.vm.resetError = { response: { status: 500 } }
        wrapper.vm.successMessage = "Hi, i'm a success !"

        await wrapper.vm.$nextTick()

        const success = wrapper.find(getByTestId('success-message'))
        expect(success.exists()).toBe(true)
        expect(success.text()).toBe("Hi, i'm a success !")

        const error = wrapper.find(getByTestId('error-message'))
        expect(error.exists()).toBe(true)

        await setFieldValues()
        await submitForm()

        await wrapper.vm.$nextTick()

        expect(wrapper.vm.resetError).toBeNull()
        expect(wrapper.vm.successMessage).toBe('')
      })
    })
  })

  describe("Navigation", () => {
    describe("Back to login button", () => {
      it('should go to /login when clicked', async () => {
        wrapper = mountComponent()

        await wrapper.find(getByTestId('go-back-button')).trigger('click')
        await testRouter.isReady()

        expect(testRouter.currentRoute.value.path).toBe('/login')
      })
    })
  })

  describe("Edge cases", () => {
    describe("Form validation", () => {
      it('should not do anything when valid is false and the form is submitted', async () => {
        wrapper = mountComponent()

        await setFieldValues({ email: "not_an_email" })
        await submitForm()

        expect(wrapper.vm.valid).toBe(false)

        expect(passwordResetService.requestReset).not.toHaveBeenCalled()
      })
    })
  })
})
