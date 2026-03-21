import { afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import BadgeElement from '../../../src/components/layouts/BadgeElement.vue'

type BadgeElementProps = {
  badgeName: string
}

const defaultProps: BadgeElementProps = {
  badgeName: 'bronze',
}

const mountComponent = ({
  props = {},
}: {
  props?: Partial<BadgeElementProps>
} = {}): VueWrapper => {
  return mount(BadgeElement, {
    props: {
      ...defaultProps,
      ...props
    }
  })
}

describe('BadgeElement component', () => {
  let wrapper: VueWrapper;

  afterEach(() => {
    wrapper?.unmount()
  })

  describe('Rendering of element', () => {
    it('should mount without any error', () => {
      wrapper = mountComponent()
      expect(true).toBe(true)
    })

    describe("Img element", () => {
      it('should be present in the DOM', () => {
        wrapper = mountComponent()
        expect(wrapper.find("img.badge-image").exists()).toBe(true)
      })

      it('should have in the alt attribute the badge name prop passed to it', () => {
        wrapper = mountComponent({
          props: {
            badgeName: 'mocked-alt-value!'
          }
        })

        expect(wrapper.get('img.badge-image').element.getAttribute('alt'))
          .toBe('mocked-alt-value!')
      })

      it('should have "50" in both "width" and "height" attributes', () => {
        wrapper = mountComponent()
        const targetElement = wrapper.get("img.badge-image").element

        expect(targetElement.getAttribute('width')).toBe('50')
        expect(targetElement.getAttribute('height')).toBe('50')
      })
    })
  })
})
