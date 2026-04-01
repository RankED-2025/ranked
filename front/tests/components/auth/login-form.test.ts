import { afterEach, describe, vi, beforeEach, MockInstance } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { getByTestId, globalTestPlugins } from '../../util/vuetify-utils'
import LoginForm from '../../../src/components/auth/LoginForm.vue'
import { createPinia, setActivePinia } from 'pinia'
import { useUserStore } from '../../../src/stores/userStore'
import { VForm } from 'vuetify/components'
import { nextTick } from 'vue'

const mountComponent = (): VueWrapper => {
  return mount(LoginForm, {
    global: {
      plugins: globalTestPlugins
    }
  })
}

type FormFieldValues = {
  email?: string|null,
  password?: string|null
}

// ------------------------------------------------------------------

describe("LoginForm Component", () => {
  let wrapper: VueWrapper;
  let userStore: ReturnType<typeof useUserStore>
  let userStoreSpy: MockInstance<typeof userStore.loginAttempt>;

  beforeEach(() => {
    setActivePinia(createPinia())
    userStore = useUserStore()
    userStoreSpy = vi.spyOn(userStore, 'loginAttempt').mockResolvedValue(true)
  })

  afterEach(() => {
    vi.clearAllMocks()
    wrapper?.unmount()
  })

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
   * Set all the forms fields to values given
   */
  const setFieldValues = async (data: FormFieldValues = {
    email: 'email@example.com',
    password: "password"
  }) => {
    await wrapper
      .get(getByTestId('email-field'))
      .find('input')
      .setValue(data.email)

    await wrapper
      .get(getByTestId('password-field'))
      .find('input')
      .setValue(data.password)
  }

  const submitForm = async () => {
    await wrapper
      .get(getByTestId('target-form'))
      .trigger('submit')
  }

  describe("Form validation", () => {
    describe("Email field", () => {

    })
  })
})
