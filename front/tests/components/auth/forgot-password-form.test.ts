import { afterEach, describe, expect, it, vi } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import ForgotPasswordForm from '../../../src/components/auth/ForgotPasswordForm.vue'
import { getByTestId, globalTestPlugins } from '../../_support/vuetify-utils'
import { nextTick } from 'vue'
import { VForm } from 'vuetify/components'

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

  const updateFormAfterDataSet = async () => {
    const formRef: VForm | undefined = wrapper.vm.$refs.formRef as VForm | undefined

    // Trigger validation manually — Vuetify won't auto-validate on setValue
    await formRef?.validate()

    // Let Vuetify flush its internal state updates
    await nextTick()
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
    })
  })
})
