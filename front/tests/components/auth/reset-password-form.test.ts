import { mount, VueWrapper } from '@vue/test-utils'
import { getByTestId, vuetifyInstance } from '../../util/vuetify-utils'
import ResetPasswordForm from '../../../src/components/auth/ResetPasswordForm.vue'
import { afterEach, beforeEach, describe, vi, it, expect, MockInstance } from 'vitest'
import { createRouter, createMemoryHistory, type Router } from 'vue-router'
import { VForm } from 'vuetify/components'
import { nextTick } from 'vue'
import { flushPromises } from '@vue/test-utils'
import { passwordResetService } from '../../../src/services/passwordResetService'

vi.mock('../../../src/services/passwordResetService')

const createTestRouter = () => {
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

const mountComponent = async (token: string | null = 'valid-token') => {
  const router = createTestRouter()

  if (token) {
    await router.push(`/reset-password?token=${token}`)
  } else {
    await router.push('/reset-password')
  }

  return {
    wrapper: mount(ResetPasswordForm, {
      global: {
        plugins: [vuetifyInstance, router],
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
  let router: Router
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

  const updateFormAfterDataSet = async () => {
    const formRef = wrapper.vm.$refs.formRef as VForm | undefined
    await formRef?.validate()
    await flushPromises()
    await nextTick()
  }

  const setFormData = async (data: {
    password?: string;
    confirmPassword?: string
  }) => {
    if (data.password !== undefined) {
      await wrapper
        .get(getByTestId('password-field'))
        .find('input')
        .setValue(data.password)
    }
    if (data.confirmPassword !== undefined) {
      await wrapper
        .get(getByTestId('confirm-password-field'))
        .find('input')
        .setValue(data.confirmPassword)
    }
    await updateFormAfterDataSet()
  }

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
        const { wrapper: comp } = await mountComponent('valid-token')
        wrapper = comp

        await wrapper.get(getByTestId('password-field')).find('input').setValue(value)
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

    describe('Confirm Password field', () => {
      it('shows error when passwords do not match', async () => {
        const { wrapper: comp } = await mountComponent('valid-token')
        wrapper = comp

        await setFormData({ password: 'ValidPass123!', confirmPassword: 'DifferentPass123!' })

        const errorElement = wrapper
          .get(getByTestId('confirm-password-field'))
          .find('.v-messages__message')

        expect(errorElement.exists()).toBe(true)
        expect(errorElement.text()).toBe('Les mots de passe ne correspondent pas')
      })

      it('shows no error when passwords match', async () => {
        const { wrapper: comp } = await mountComponent('valid-token')
        wrapper = comp

        await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })

        const errorElement = wrapper
          .get(getByTestId('confirm-password-field'))
          .find('.v-messages__message')

        expect(errorElement.exists()).toBe(false)
      })

      it('shows required error when empty', async () => {
        const { wrapper: comp } = await mountComponent('valid-token')
        wrapper = comp

        await setFormData({ password: 'ValidPass123!', confirmPassword: '' })

        const errorElement = wrapper
          .get(getByTestId('confirm-password-field'))
          .find('.v-messages__message')

        expect(errorElement.exists()).toBe(true)
        expect(errorElement.text()).toBe('La confirmation du mot de passe est requise')
      })
    })
  })

  describe('Form submission', () => {
    it('disables submit button when form is invalid', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      await nextTick()

      expect(wrapper.get(getByTestId('submit-button')).attributes('disabled'))
        .toBeDefined()
    })

    it('enables submit button when form is valid', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })

      expect(wrapper.get(getByTestId('submit-button')).attributes('disabled'))
        .toBeUndefined()
    })

    describe('successful password reset', () => {
      beforeEach(async () => {
        const { wrapper: comp, router: r } = await mountComponent('valid-token')
        wrapper = comp
        router = r

        await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })
      })

      it('calls confirmReset with correct data', async () => {
        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        expect(confirmResetSpy).toHaveBeenCalledWith({
          token: 'valid-token',
          password: 'ValidPass123!',
        })
      })

      it('shows success message', async () => {
        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        const successAlert = wrapper.get(getByTestId('success-alert'))
        expect(successAlert.text()).toContain('Mot de passe réinitialisé avec succès')
      })

      it('sets loading state during submission', async () => {
        let resolveConfirmReset!: (value: Record<string, string>) => void

        confirmResetSpy.mockImplementation(
          () => new Promise(resolve => { resolveConfirmReset = resolve })
        )

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.get(getByTestId('submit-button')).classes())
          .toContain('v-btn--loading')

        resolveConfirmReset({ message: 'done' })
        await flushPromises()

        expect(wrapper.get(getByTestId('submit-button')).classes())
          .not.toContain('v-btn--loading')
      })

      it('disables inputs during loading', async () => {
        let resolveConfirmReset!: (value: Record<string, string>) => void
        confirmResetSpy.mockImplementation(
          () => new Promise(resolve => { resolveConfirmReset = resolve })
        )

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        const inputs = wrapper.findAll('input')
        inputs.forEach((input) => {
          expect(input.attributes('disabled')).toBeDefined()
        })

        resolveConfirmReset({ message: 'done' })
        await flushPromises()
      })

      it('redirects to login after 2 seconds', async () => {
        const routerPushSpy = vi.spyOn(router, 'push')

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        vi.advanceTimersByTime(4000)
        await flushPromises()

        expect(routerPushSpy).toHaveBeenCalledWith('/login')
      })

      it('clears error message before successful reset', async () => {
        wrapper.vm.resetError = { response: { status: 500 } }
        await nextTick()

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.find(getByTestId('error-alert')).exists()).toBe(false)
      })

      it('sets success message after successful reset', async () => {
        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.get(getByTestId('success-alert')).text())
          .toContain('Mot de passe réinitialisé avec succès')
      })
    })

    describe('failed password reset', () => {
      beforeEach(async () => {
        const { wrapper: comp, router: r } = await mountComponent('valid-token')
        wrapper = comp
        router = r

        await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })
      })

      it('shows the overridden message when the token is invalid or expired (400)', async () => {
        confirmResetSpy.mockRejectedValue({ response: { status: 400 } })

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.get(getByTestId('error-alert')).text())
          .toBe('Le lien de réinitialisation est invalide ou a expiré.')
      })

      it('does not redirect on failure', async () => {
        confirmResetSpy.mockRejectedValue(new Error('Token invalide'))
        const routerPushSpy = vi.spyOn(router, 'push')

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        vi.advanceTimersByTime(4000)
        await flushPromises()

        expect(routerPushSpy).not.toHaveBeenCalledWith('/login')
      })

      it('sets loading to false on error', async () => {
        confirmResetSpy.mockRejectedValue(new Error('Error'))

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.get(getByTestId('submit-button')).classes())
          .not.toContain('v-btn--loading')
      })
    })

    describe('invalid or missing token', () => {
      it('shows error message when token is missing', async () => {
        const { wrapper: comp } = await mountComponent(null)
        wrapper = comp

        await nextTick()

        expect(wrapper.get(getByTestId('error-alert')).text())
          .toContain('Token de réinitialisation manquant')
      })

      it('does not call confirmReset when form is invalid', async () => {
        const { wrapper: comp } = await mountComponent('valid-token')
        wrapper = comp

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        expect(confirmResetSpy).not.toHaveBeenCalled()
      })

      it('shows error message when token is missing on submit', async () => {
        const { wrapper: comp } = await mountComponent(null)
        wrapper = comp

        wrapper.vm.valid = true
        await nextTick()

        await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
        await flushPromises()

        expect(wrapper.get(getByTestId('error-alert')).text())
          .toContain('Token invalide ou manquant')
      })
    })
  })

  describe('Navigation', () => {
    it('navigates to login when goToLogin button is clicked', async () => {
      const { wrapper: comp, router: r } = await mountComponent('valid-token')
      wrapper = comp
      router = r

      const routerPushSpy = vi.spyOn(router, 'push')

      await wrapper.get(getByTestId('login-button')).trigger('click')
      await flushPromises()

      expect(routerPushSpy).toHaveBeenCalledWith('/login')
    })

    it('disables goToLogin button during loading', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.loading = true
      await nextTick()

      expect(wrapper.get(getByTestId('login-button')).attributes('disabled')).toBeDefined()
    })
  })

  describe('Message display', () => {
    it('displays success alert when successMessage is set', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.successMessage = 'Test success message'
      await nextTick()

      expect(wrapper.get(getByTestId('success-alert')).text())
        .toBe('Test success message')
    })

    it('displays error alert when resetError is set', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.resetError = { response: { status: 500 } }
      await nextTick()

      expect(wrapper.get(getByTestId('error-alert')).text())
        .toBe('Une erreur interne est survenue. Veuillez réessayer plus tard.')
    })

    it('does not display success alert when successMessage is empty', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.successMessage = ''
      await nextTick()

      expect(wrapper.find(getByTestId('success-alert')).exists()).toBe(false)
    })

    it('does not display error alert when resetError is null', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.resetError = null
      await nextTick()

      expect(wrapper.find(getByTestId('error-alert')).exists()).toBe(false)
    })
  })

  describe('Loading state', () => {
    it('disables submit button when loading', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.loading = true
      await nextTick()

      expect(wrapper.get(getByTestId('submit-button')).attributes('disabled'))
        .toBeDefined()
    })

    it('shows loading state on submit button', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })

      let resolveConfirmReset!: (value: Record<string, string>) => void
      confirmResetSpy.mockImplementation(
        () => new Promise(resolve => { resolveConfirmReset = resolve })
      )

      await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
      await flushPromises()

      expect(wrapper.get(getByTestId('submit-button')).classes())
        .toContain('v-btn--loading')

      resolveConfirmReset({ message: 'done' })
      await flushPromises()
    })
  })

  describe('Token handling', () => {
    it('extracts token from query parameter', async () => {
      const { wrapper: comp } = await mountComponent('test-token-123')
      wrapper = comp

      await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })
      await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
      await flushPromises()

      expect(confirmResetSpy).toHaveBeenCalledWith({
        token: 'test-token-123',
        password: 'ValidPass123!',
      })
    })

    it('shows error when token is missing on mount', async () => {
      const { wrapper: comp } = await mountComponent(null)
      wrapper = comp

      await nextTick()

      expect(wrapper.get(getByTestId('error-alert')).text())
        .toContain('Token de réinitialisation manquant')
    })
  })

  describe('Form reset', () => {
    it('clears all messages before submission', async () => {
      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      wrapper.vm.resetError = { response: { status: 500 } }
      wrapper.vm.successMessage = 'Old success'
      await nextTick()

      await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })

      await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
      await flushPromises()

      expect(wrapper.find(getByTestId('error-alert')).exists()).toBe(false)
      expect(wrapper.get(getByTestId('success-alert')).text())
        .toBe('Mot de passe réinitialisé avec succès')
    })
  })

  describe('Edge cases', () => {
    it('handles empty error message gracefully', async () => {
      confirmResetSpy.mockRejectedValue(new Error(''))

      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })

      await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
      await flushPromises()

      expect(wrapper.get(getByTestId('error-alert')).text())
        .toBe('Une erreur est survenue. Veuillez réessayer.')
    })

    it('handles successful response with custom message', async () => {
      const customMessage = 'Votre mot de passe a été réinitialisé'
      confirmResetSpy.mockResolvedValue({ message: customMessage })

      const { wrapper: comp } = await mountComponent('valid-token')
      wrapper = comp

      await setFormData({ password: 'ValidPass123!', confirmPassword: 'ValidPass123!' })

      await wrapper.get(getByTestId('reset-password-form')).trigger('submit')
      await flushPromises()

      expect(wrapper.get(getByTestId('success-alert')).text()).toBe(customMessage)
    })
  })
})
