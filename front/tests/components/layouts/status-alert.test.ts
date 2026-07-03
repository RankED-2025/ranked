import { afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { VAlert } from 'vuetify/components'
import { vuetifyInstance, getByTestId } from '../../util/vuetify-utils'
import StatusAlert from '../../../src/components/layouts/StatusAlert.vue'
import { DEFAULT_STATUS_MESSAGES, NETWORK_ERROR_MESSAGE, FALLBACK_STATUS_MESSAGE } from '../../../src/utils'
import type { StatusMessageOverride } from '../../../src/types'
import { defaultStatusMessageCases } from '../../util/status-messages'

type Props = {
  error?: unknown
  overrides?: StatusMessageOverride[]
  testId?: string
}

const mountComponent = (props: Props = {}): VueWrapper =>
  mount(StatusAlert, {
    props,
    global: {
      plugins: [vuetifyInstance],
    },
  })

// A minimal network-error shape: axios.isAxiosError() only checks `isAxiosError === true`,
// so a plain object satisfies it without needing a real failed request.
const networkError = {
  isAxiosError: true,
  request: {},
}

// ------------------------------------------------------------------------------

describe('StatusAlert component', () => {
  let wrapper: VueWrapper

  afterEach(() => {
    wrapper?.unmount()
  })

  describe('Rendering', () => {
    it('should not render an alert when error is null', () => {
      wrapper = mountComponent({ error: null })
      expect(wrapper.findComponent(VAlert).exists()).toBe(false)
    })

    it('should not render an alert when error is undefined', () => {
      wrapper = mountComponent()
      expect(wrapper.findComponent(VAlert).exists()).toBe(false)
    })

    it('should render an alert when error is set', () => {
      wrapper = mountComponent({ error: { response: { status: 500 } } })
      expect(wrapper.findComponent(VAlert).exists()).toBe(true)
    })

    it('should use "error-message" as the default data-testid', () => {
      wrapper = mountComponent({ error: { response: { status: 500 } } })
      expect(wrapper.find(getByTestId('error-message')).exists()).toBe(true)
    })

    it('should use the provided testId prop for the data-testid attribute', () => {
      wrapper = mountComponent({ error: { response: { status: 500 } }, testId: 'custom-alert' })
      expect(wrapper.find(getByTestId('custom-alert')).exists()).toBe(true)
      expect(wrapper.find(getByTestId('error-message')).exists()).toBe(false)
    })

    it('should be closable', () => {
      wrapper = mountComponent({ error: { response: { status: 500 } } })
      expect(wrapper.findComponent(VAlert).props('closable')).toBe(true)
    })
  })

  describe('Default status messages', () => {
    it.each(
      defaultStatusMessageCases()
    )('should display the $type message for status $status', ({ status, message, type }) => {
      wrapper = mountComponent({ error: { response: { status } } })
      expect(wrapper.text()).toBe(message)
      expect(wrapper.findComponent(VAlert).props('type')).toBe(type)
    })
  })

  describe('Overrides', () => {
    it('should use the override message and type instead of the default for a matching status', () => {
      const overrides: StatusMessageOverride[] = [
        { status: 500, type: 'warning', message: 'Custom overridden message' },
      ]

      wrapper = mountComponent({ error: { response: { status: 500 } }, overrides })

      expect(wrapper.text()).toBe('Custom overridden message')
      expect(wrapper.findComponent(VAlert).props('type')).toBe('warning')
    })

    it('should fall back to the default message when no override matches the status', () => {
      const overrides: StatusMessageOverride[] = [
        { status: 401, type: 'warning', message: 'Custom overridden message' },
      ]

      wrapper = mountComponent({ error: { response: { status: 500 } }, overrides })

      expect(wrapper.text()).toBe(DEFAULT_STATUS_MESSAGES[500].message)
    })

    it('should use only the default messages when the overrides prop is omitted', () => {
      wrapper = mountComponent({ error: { response: { status: 401 } } })
      expect(wrapper.text()).toBe(DEFAULT_STATUS_MESSAGES[401].message)
    })
  })

  describe('Network and fallback errors', () => {
    it('should display the network error message for an axios error without a response', () => {
      wrapper = mountComponent({ error: networkError })

      expect(wrapper.text()).toBe(NETWORK_ERROR_MESSAGE.message)
      expect(wrapper.findComponent(VAlert).props('type')).toBe(NETWORK_ERROR_MESSAGE.type)
    })

    it('should display the fallback message for an unrecognized status', () => {
      wrapper = mountComponent({ error: { response: { status: 999 } } })
      expect(wrapper.text()).toBe(FALLBACK_STATUS_MESSAGE.message)
    })

    it('should display the fallback message when the error has no status and is not a network error', () => {
      wrapper = mountComponent({ error: {} })
      expect(wrapper.text()).toBe(FALLBACK_STATUS_MESSAGE.message)
    })

    it('should display the fallback message for a plain Error instance', () => {
      wrapper = mountComponent({ error: new Error('boom') })
      expect(wrapper.text()).toBe(FALLBACK_STATUS_MESSAGE.message)
    })

    it.each([
      { error: 'a string error' },
      { error: 42 },
      { error: true },
    ])('should display the fallback message for a non-object error ($error)', ({ error }) => {
      wrapper = mountComponent({ error })
      expect(wrapper.text()).toBe(FALLBACK_STATUS_MESSAGE.message)
    })
  })

  describe('Events', () => {
    it('should emit "update:error" with null when the close button is clicked', async () => {
      wrapper = mountComponent({ error: { response: { status: 500 } } })

      await wrapper.get('.v-alert__close button').trigger('click')

      expect(wrapper.emitted('update:error')).toEqual([[null]])
    })

    it('should not emit "update:error" before the close button is clicked', () => {
      wrapper = mountComponent({ error: { response: { status: 500 } } })
      expect(wrapper.emitted('update:error')).toBeUndefined()
    })
  })

  describe('Reactivity', () => {
    it('should update the displayed message when the error prop changes', async () => {
      wrapper = mountComponent({ error: { response: { status: 401 } } })
      expect(wrapper.text()).toBe(DEFAULT_STATUS_MESSAGES[401].message)

      await wrapper.setProps({ error: { response: { status: 500 } } })
      expect(wrapper.text()).toBe(DEFAULT_STATUS_MESSAGES[500].message)
    })

    it('should hide the alert when the error prop is cleared to null', async () => {
      wrapper = mountComponent({ error: { response: { status: 500 } } })
      expect(wrapper.findComponent(VAlert).exists()).toBe(true)

      await wrapper.setProps({ error: null })
      expect(wrapper.findComponent(VAlert).exists()).toBe(false)
    })

    it('should re-evaluate against new overrides when the overrides prop changes', async () => {
      wrapper = mountComponent({ error: { response: { status: 500 } } })
      expect(wrapper.text()).toBe(DEFAULT_STATUS_MESSAGES[500].message)

      await wrapper.setProps({
        overrides: [{ status: 500, type: 'warning', message: 'Now overridden' }],
      })

      expect(wrapper.text()).toBe('Now overridden')
      expect(wrapper.findComponent(VAlert).props('type')).toBe('warning')
    })
  })
})
