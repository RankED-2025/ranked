import { afterEach, describe, expect, it, vi } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import ForgotPasswordForm from '../../../src/components/auth/ForgotPasswordForm.vue'
import { getByTestId, globalTestPlugins } from '../../_support/vuetify-utils'
import { nextTick } from 'vue'
import { VForm } from 'vuetify/components'
import { passwordResetService } from '../../../src/services/passwordResetService'

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

describe('ForgotPasswordForm component', () => {
  let wrapper: VueWrapper;

  /**
   * Updates the component after setting a field value
   */
  const updateFormAfterDataSet = async () => {
    const formRef: VForm | undefined = wrapper.vm.$refs.formRef as VForm | undefined

    // Trigger validation manually — Vuetify won't auto-validate on setValue
    await formRef?.validate()

    // Let Vuetify flush its internal state updates
    await nextTick()
  }

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

    await updateFormAfterDataSet()
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

          const field = wrapper.get(getByTestId('email-field')).find('.v-messages__message')

          expect(field.exists()).toBe(true)
          expect(field.text()).toBe(message)
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
    it('should display the error message when an error is thrown when doing the request', async () => {
      wrapper = mountComponent()

      vi.mocked(passwordResetService.requestReset)
        .mockThrow(new Error('I am a displayed error, yepee !'))

      await setFieldValues({ email: 'john@example.com' })
      await submitForm()
      await updateFormAfterDataSet()

      const errorMessage = wrapper.find(getByTestId('error-message'))

      expect(errorMessage.exists()).toBe(true)
      expect(errorMessage.text()).toBe('I am a displayed error, yepee !')
    })

    it('should display the error message with a fallback when no messages are provided on the thrown error', async () => {
      wrapper = mountComponent()

      vi.mocked(passwordResetService.requestReset)
        .mockThrow(new Error(undefined))

      await setFieldValues({ email: 'john@example.com' })
      await submitForm()
      await updateFormAfterDataSet()

      const errorMessage = wrapper.find(getByTestId('error-message'))

      expect(errorMessage.exists()).toBe(true)
      expect(errorMessage.text()).toBe('Une erreur est survenue')
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
        .mockThrow(new Error('I am a displayed error, yepee !'))

      await setFieldValues({ email: 'john@example.com' })
      await submitForm()
      await updateFormAfterDataSet()

      expect(wrapper.get(getByTestId('email-field')).find('input').element.value)
        .toBe('john@example.com')

      expect(wrapper.vm.email).toBe('john@example.com')
    })
  })
})
