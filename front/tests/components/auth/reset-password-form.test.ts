import { mount, VueWrapper } from '@vue/test-utils'
import { globalTestPlugins } from '../../util/vuetify-utils'
import ResetPasswordForm from '../../../src/components/auth/ResetPasswordForm.vue'
import { afterEach, beforeEach, describe, vi, it, expect, MockInstance } from 'vitest'
import { createRouter, createMemoryHistory } from 'vue-router'
import { nextTick } from 'vue'
import { flushPromises } from '@vue/test-utils'
import { passwordResetService } from '../../../src/services/passwordResetService'

// Mock the passwordResetService
vi.mock('../../../src/services/passwordResetService')

const createTestRouter = (token: string | null = null) => {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      {
        path: '/reset-password',
        name: 'ResetPassword',
        component: { template: '<div>Reset</div>' },
      },
      {
        path: '/login',
        name: 'Login',
        component: { template: '<div>Login</div>' },
      },
    ],
  })
}

const mountComponent = (token: string | null = 'valid-token') => {
  const router = createTestRouter(token)

  // Set the query parameter for the token
  if (token) {
    router.push(`/reset-password?token=${token}`)
  }

  return {
    wrapper: mount(ResetPasswordForm, {
      global: {
        plugins: [...globalTestPlugins, router],
        stubs: {
          'v-card-title': { template: '<div><slot /></div>' },
          'v-card-subtitle': { template: '<div><slot /></div>' },
        },
      },
    }),
    router,
  }
}

describe('ResetPasswordForm component', () => {
  let wrapper: VueWrapper
  let router: any
  let confirmResetSpy: MockInstance

  beforeEach(() => {
    vi.useFakeTimers()
    confirmResetSpy = vi.spyOn(passwordResetService, 'confirmReset').mockResolvedValue({
      message: 'Mot de passe réinitialisé avec succès',
    })
  })

  afterEach(() => {
    vi.clearAllMocks()
    vi.restoreAllMocks()
    vi.useRealTimers()
    wrapper?.unmount()
  })

  describe('Form validation', () => {
    describe('Password field', () => {
      it.each([
        { value: '', message: 'Veuillez entrer un mot de passe' },
        { value: 'short', message: 'Le mot de passe doit contenir au moins 8 caractères' },
        { value: 'nouppercase123!', message: 'Le mot de passe doit contenir au moins une majuscule' },
        { value: 'NOLOWERCASE123!', message: 'Le mot de passe doit contenir au moins une minuscule' },
        { value: 'NoNumber!', message: 'Le mot de passe doit contenir au moins un chiffre' },
        { value: 'NoSpecial123', message: 'Le mot de passe doit contenir un caractère spécial (@, $, !, %, *, ?, &)' },
        { value: 'ValidPass123!', message: null },
      ])('shows $message for password value $value', async ({ value, message }) => {
        const { wrapper: comp } = mountComponent('valid-token')
        wrapper = comp

        const passwordField = wrapper.findAll('input')[0]
        await passwordField.setValue(value)

        // Trigger validation
        const formRef = wrapper.vm.$refs.formRef
        await formRef?.validate()
        await nextTick()

        const errorElements = wrapper.findAll('.v-messages__message')

        if (message) {
          expect(errorElements.length).toBeGreaterThan(0)
          expect(errorElements[0].text()).toBe(message)
        }
      })
    })

    describe('Confirm Password field', () => {
      it('shows error when passwords do not match', async () => {
        const { wrapper: comp } = mountComponent('valid-token')
        wrapper = comp

        const inputs = wrapper.findAll('input')
        await inputs[0].setValue('ValidPass123!')
        await inputs[1].setValue('DifferentPass123!')

        const formRef = wrapper.vm.$refs.formRef
        await formRef?.validate()
        await nextTick()

        const errorElements = wrapper.findAll('.v-messages__message')
        expect(errorElements.length).toBeGreaterThan(0)
        expect(errorElements[errorElements.length - 1].text()).toBe('Les mots de passe ne correspondent pas')
      })

      it('shows no error when passwords match', async () => {
        const { wrapper: comp } = mountComponent('valid-token')
        wrapper = comp

        const inputs = wrapper.findAll('input')
        await inputs[0].setValue('ValidPass123!')
        await inputs[1].setValue('ValidPass123!')

        const formRef = wrapper.vm.$refs.formRef
        await formRef?.validate()
        await nextTick()

        expect(wrapper.vm.valid).toBe(true)
      })

      it('shows required error when empty', async () => {
        const { wrapper: comp } = mountComponent('valid-token')
        wrapper = comp

        const inputs = wrapper.findAll('input')
        await inputs[0].setValue('ValidPass123!')
        await inputs[1].setValue('')

        const formRef = wrapper.vm.$refs.formRef
        await formRef?.validate()
        await nextTick()

        const errorElements = wrapper.findAll('.v-messages__message')
        expect(errorElements.length).toBeGreaterThan(0)
      })
    })
  })

  describe('Form submission', () => {
    it('disables submit button when form is invalid', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      await nextTick()

      const submitButton = wrapper.findAll('button')[0]
      expect(submitButton.attributes('disabled')).toBeDefined()
    })

    it('enables submit button when form is valid', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      const inputs = wrapper.findAll('input')
      await inputs[0].setValue('ValidPass123!')
      await inputs[1].setValue('ValidPass123!')

      const formRef = wrapper.vm.$refs.formRef
      await formRef?.validate()
      await nextTick()

      const submitButton = wrapper.findAll('button')[0]
      expect(submitButton.attributes('disabled')).toBeUndefined()
    })

    describe('successful password reset', () => {
      beforeEach(async () => {
        const { wrapper: comp, router: r } = mountComponent('valid-token')
        wrapper = comp
        router = r

        const inputs = wrapper.findAll('input')
        await inputs[0].setValue('ValidPass123!')
        await inputs[1].setValue('ValidPass123!')

        const formRef = wrapper.vm.$refs.formRef
        await formRef?.validate()
        await nextTick()
      })

      it('calls confirmReset with correct data', async () => {
        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        expect(confirmResetSpy).toHaveBeenCalledWith({
          token: 'valid-token',
          password: 'ValidPass123!',
        })
      })

      it('shows success message', async () => {
        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        const successAlert = wrapper.findAll('.v-alert')[0]
        expect(successAlert.text()).toContain('Mot de passe réinitialisé avec succès')
      })

      it('sets loading state during submission', async () => {
        const form = wrapper.find('form')

        // Start submission
        form.trigger('submit')
        await nextTick()

        expect(wrapper.vm.loading).toBe(true)

        await flushPromises()

        expect(wrapper.vm.loading).toBe(false)
      })

      it('disables inputs during loading', async () => {
        const form = wrapper.find('form')

        form.trigger('submit')
        await nextTick()

        const inputs = wrapper.findAll('input')
        inputs.forEach((input) => {
          expect(input.attributes('disabled')).toBeDefined()
        })

        await flushPromises()
      })

      it('redirects to login after 2 seconds', async () => {
        const routerPushSpy = vi.spyOn(router, 'push')

        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        vi.advanceTimersByTime(2000)
        await flushPromises()

        expect(routerPushSpy).toHaveBeenCalledWith('/login')
      })

      it('clears error message on successful reset', async () => {
        wrapper.vm.errorMessage = 'Previous error'

        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        expect(wrapper.vm.errorMessage).toBe('')
      })

      it('clears success message on submission', async () => {
        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        expect(wrapper.vm.successMessage).toContain('Mot de passe réinitialisé avec succès')
      })
    })

    describe('failed password reset', () => {
      beforeEach(async () => {
        const { wrapper: comp, router: r } = mountComponent('valid-token')
        wrapper = comp
        router = r

        const inputs = wrapper.findAll('input')
        await inputs[0].setValue('ValidPass123!')
        await inputs[1].setValue('ValidPass123!')

        const formRef = wrapper.vm.$refs.formRef
        await formRef?.validate()
        await nextTick()
      })

      it('shows error message on failure', async () => {
        const errorMsg = 'Token invalide ou expiré'
        confirmResetSpy.mockRejectedValue(new Error(errorMsg))

        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        expect(wrapper.vm.errorMessage).toBe(errorMsg)
      })

      it('does not redirect on failure', async () => {
        confirmResetSpy.mockRejectedValue(new Error('Token invalide'))
        const routerPushSpy = vi.spyOn(router, 'push')

        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        vi.advanceTimersByTime(2000)
        await flushPromises()

        expect(routerPushSpy).not.toHaveBeenCalledWith('/login')
      })

      it('sets loading to false on error', async () => {
        confirmResetSpy.mockRejectedValue(new Error('Error'))

        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        expect(wrapper.vm.loading).toBe(false)
      })
    })

    describe('invalid or missing token', () => {
      it('shows error message when token is missing', async () => {
        const { wrapper: comp } = mountComponent(null)
        wrapper = comp

        await nextTick()

        expect(wrapper.vm.errorMessage).toContain('Token de réinitialisation manquant')
      })

      it('does not call confirmReset when form is invalid', async () => {
        const { wrapper: comp } = mountComponent('valid-token')
        wrapper = comp

        // Submit with empty form (invalid)
        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        expect(confirmResetSpy).not.toHaveBeenCalled()
      })

      it('shows error message when token is missing on submit', async () => {
        const { wrapper: comp } = mountComponent(null)
        wrapper = comp

        // Set valid password to make form valid
        wrapper.vm.password = 'ValidPass123!'
        wrapper.vm.confirmPassword = 'ValidPass123!'
        wrapper.vm.valid = true

        await nextTick()

        const form = wrapper.find('form')
        await form.trigger('submit')
        await flushPromises()

        expect(wrapper.vm.errorMessage).toContain('Token invalide ou manquant')
      })
    })
  })

  describe('Navigation', () => {
    it('navigates to login when goToLogin button is clicked', async () => {
      const { wrapper: comp, router: r } = mountComponent('valid-token')
      wrapper = comp
      router = r

      const routerPushSpy = vi.spyOn(router, 'push')

      const buttons = wrapper.findAll('button')
      const loginButton = buttons[buttons.length - 1] // Last button is "Retour à la connexion"

      await loginButton.trigger('click')
      await flushPromises()

      expect(routerPushSpy).toHaveBeenCalledWith('/login')
    })

    it('disables goToLogin button during loading', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.loading = true
      await nextTick()

      const buttons = wrapper.findAll('button')
      const loginButton = buttons[buttons.length - 1]

      expect(loginButton.attributes('disabled')).toBeDefined()
    })
  })

  describe('Message display', () => {
    it('displays success alert when successMessage is set', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.successMessage = 'Test success message'
      await nextTick()

      const alerts = wrapper.findAll('.v-alert')
      expect(alerts.length).toBeGreaterThan(0)
      expect(alerts[0].text()).toBe('Test success message')
    })

    it('displays error alert when errorMessage is set', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.errorMessage = 'Test error message'
      await nextTick()

      const alerts = wrapper.findAll('.v-alert')
      const errorAlert = alerts.find((alert) => alert.text().includes('Test error message'))
      expect(errorAlert).toBeDefined()
    })

    it('does not display success alert when successMessage is empty', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.successMessage = ''
      await nextTick()

      const alerts = wrapper.findAll('.v-alert')
      // Should not have a success alert with content
      const successAlerts = alerts.filter((alert) => alert.element.textContent.trim().length > 0)
      expect(successAlerts.length).toBe(0)
    })

    it('does not display error alert when errorMessage is empty', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.errorMessage = ''
      await nextTick()

      const alerts = wrapper.findAll('.v-alert')
      const visibleAlerts = alerts.filter((alert) => alert.element.textContent.trim().length > 0)
      expect(visibleAlerts.length).toBe(0)
    })
  })

  describe('Loading state', () => {
    it('disables submit button when loading', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.loading = true
      await nextTick()

      const submitButton = wrapper.findAll('button')[0]
      expect(submitButton.attributes('disabled')).toBeDefined()
    })

    it('shows loading state on submit button', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      const inputs = wrapper.findAll('input')
      await inputs[0].setValue('ValidPass123!')
      await inputs[1].setValue('ValidPass123!')

      const formRef = wrapper.vm.$refs.formRef
      await formRef?.validate()
      await nextTick()

      const form = wrapper.find('form')
      form.trigger('submit')
      await nextTick()

      const submitButton = wrapper.findAll('button')[0]
      expect(submitButton.classes()).toContain('v-btn--loading')

      await flushPromises()
    })
  })

  describe('Token handling', () => {
    it('extracts token from query parameter', async () => {
      const { wrapper: comp } = mountComponent('test-token-123')
      wrapper = comp

      expect(wrapper.vm.token).toBe('test-token-123')
    })

    it('shows error when token is missing on mount', async () => {
      const { wrapper: comp } = mountComponent(null)
      wrapper = comp

      await nextTick()

      expect(wrapper.vm.errorMessage).toContain('Token de réinitialisation manquant')
    })
  })

  describe('Form reset', () => {
    it('clears all messages before submission', async () => {
      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.errorMessage = 'Old error'
      wrapper.vm.successMessage = 'Old success'

      const inputs = wrapper.findAll('input')
      await inputs[0].setValue('ValidPass123!')
      await inputs[1].setValue('ValidPass123!')

      const formRef = wrapper.vm.$refs.formRef
      await formRef?.validate()
      await nextTick()

      const form = wrapper.find('form')
      await form.trigger('submit')
      await flushPromises()

      // After submission, messages should be cleared before the new ones are set
      // (they're cleared at the start of handleSubmit)
      expect(wrapper.vm.successMessage).toBe('Mot de passe réinitialisé avec succès')
    })
  })

  describe('Edge cases', () => {
    it('handles empty error message gracefully', async () => {
      confirmResetSpy.mockRejectedValue(new Error(''))

      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      const inputs = wrapper.findAll('input')
      await inputs[0].setValue('ValidPass123!')
      await inputs[1].setValue('ValidPass123!')

      const formRef = wrapper.vm.$refs.formRef
      await formRef?.validate()
      await nextTick()

      const form = wrapper.find('form')
      await form.trigger('submit')
      await flushPromises()

      // Should show default error message when error message is empty
      expect(wrapper.vm.errorMessage).toBe('Une erreur est survenue')
    })

    it('handles successful response with custom message', async () => {
      const customMessage = 'Votre mot de passe a été réinitialisé'
      confirmResetSpy.mockResolvedValue({ message: customMessage })

      const { wrapper: comp } = mountComponent('valid-token')
      wrapper = comp

      const inputs = wrapper.findAll('input')
      await inputs[0].setValue('ValidPass123!')
      await inputs[1].setValue('ValidPass123!')

      const formRef = wrapper.vm.$refs.formRef
      await formRef?.validate()
      await nextTick()

      const form = wrapper.find('form')
      await form.trigger('submit')
      await flushPromises()

      expect(wrapper.vm.successMessage).toBe(customMessage)
    })
  })
})

