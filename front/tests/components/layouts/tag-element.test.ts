import TagElement from '../../../src/components/layouts/TagElement.vue'
import { mount, VueWrapper } from '@vue/test-utils'
import { afterEach, describe, expect, it } from 'vitest'

type TagElementProps = typeof TagElement.props

const defaultProps: TagElementProps = {
  test: 'Test',
  color: 'primary',
  textColor: 'black',
  size: 'medium'
}

/**
 * Mount the component with params
 */
const mountComponent = ({
  props = {},
}: {
  props?: Partial<TagElementProps>
} = {}): VueWrapper => {
  return mount(TagElement, {
    props: {
      ...defaultProps,
      ...props
    }
  })
}

// ------------------------------------------------------------------

describe("TagElement component", () => {
  let wrapper: VueWrapper;

  afterEach(() => {
    wrapper?.unmount()
  })

  describe("Rendering", () => {

    it('should mount without any error', () => {
      wrapper = mountComponent()
      expect(true).toBe(true)
    })

    it('should be rendered as a <span> element', () => {
      wrapper = mountComponent()

      expect(wrapper.element.tagName.toLowerCase()).toBe('span')
    })

    it('should have a "tag-element" class', () => {
      wrapper = mountComponent()

      expect(wrapper.element.classList).toContain('tag-element')
    })

    it('should have its inner text match what is in the "text" prop', () => {
      wrapper = mountComponent({
        props: {
          text: "Hey, i'm a test value, yepee!"
        }
      })

      expect(wrapper.text()).toBe("Hey, i'm a test value, yepee!")
    })
  })

  describe("classes", () => {
    describe("size", () => {
      it.each([
        { size: 'small', expected: 'size-small' },
        { size: 'medium', expected: 'size-medium' },
        { size: 'large', expected: 'size-large' },
      ])('should apply the $expected class when given the $size size', ({ size, expected }) => {
        wrapper = mountComponent({
          props: {
            size: size,
          }
        })

        expect(wrapper.element.classList).toContain(expected)
      })

      it('should fall back to the "medium" size when the "size" prop is undefined', () => {
        wrapper = mountComponent({
          props: {
            size: undefined,
          }
        })

        expect(wrapper.element.classList).toContain('size-medium')
      })
    })
  })
})
