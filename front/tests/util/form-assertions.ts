import type { VueWrapper } from '@vue/test-utils'
import { expect } from 'vitest'
import { getByTestId } from './vuetify-utils'

/**
 * Asserts a field's Vuetify validation message: present with the given text,
 * or absent when message is null/undefined.
 */
export const expectFieldValidationMessage = (
  wrapper: VueWrapper,
  testId: string,
  message?: string | null
) => {
  const errorElement = wrapper.get(getByTestId(testId)).find('.v-messages__message')

  if (message) {
    expect(errorElement.exists()).toBe(true)
    expect(errorElement.text()).toBe(message)
  } else {
    expect(errorElement.exists()).toBe(false)
  }
}
