import { afterEach, describe, expect, it, vi } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import ForgotPasswordForm from '../../../src/components/auth/ForgotPasswordForm.vue'
import { getByTestId, globalTestPlugins } from '../../_support/vuetify-utils'

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
})
