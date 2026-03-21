import IconElement from '../../../src/components/layouts/IconElement.vue'
import { mount, VueWrapper } from '@vue/test-utils'
import { afterEach, describe, expect, it } from 'vitest'

type IconElementProps = typeof IconElement.props

const defaultProps: IconElementProps = {
  name: 'home',
  size: 'medium',
  color: 'primary',
  title: 'Icon',
  ariaLabel: 'icon'
}

/**
 * Mount the component with params
 */
const mountComponent = ({
  props = {},
}: {
  props?: Partial<IconElementProps>
} = {}): VueWrapper => {
  return mount(IconElement, {
    props: {
      ...defaultProps,
      ...props
    }
  })
}


// ------------------------------------------------------------------

describe('IconElement component', () => {
  let wrapper: VueWrapper;

  afterEach(() => {
    wrapper?.unmount()
  })

  describe('Rendering', () => {
    it('should mount without any error', () => {
      wrapper = mountComponent()
      expect(true).toBe(true)
    })

    it('should render as a <i> tag', () => {
      wrapper = mountComponent()
      expect(wrapper.element.tagName.toLowerCase()).toBe('i')
    })

    it('should have the "icon-element" and "mdi" classes', () => {
      wrapper = mountComponent()

      const classes: DOMTokenList = wrapper.element.classList

      expect(classes).toContain('icon-element')
      expect(classes).toContain('mdi')
    })

    describe("props in the DOM", () => {
      describe("'title' prop", () => {
        it("should have it's 'title' prop matching the attribute 'title' in the DOM", () => {
          wrapper = mountComponent({
            props: {
              title: "i'm a mocked title, yay!"
            }
          })

          expect(wrapper.element.getAttribute('title'))
            .toBe("i'm a mocked title, yay!")
        })
      })

      describe("'aria-label' prop", () => {
        it("should have it's 'ariaLabel' prop matching the attribute 'ariaLabel' in the DOM", () => {
          wrapper = mountComponent({
            props: {
              ariaLabel: "i'm a mocked aria-label, yay!",
              title: 'i should not be seen !'
            }
          })

          expect(wrapper.element.getAttribute("aria-label"))
            .toBe("i'm a mocked aria-label, yay!")
        })

        it.each([
          { prop: undefined },
          { prop: null },
        ])('should fallback to the "title" prop when aria label is $prop', ({ prop }) => {
          wrapper = mountComponent({
            props: {
              ariaLabel: prop,
              title: 'i am the fallback title !'
            }
          })

          expect(wrapper.element.getAttribute("aria-label"))
            .toBe('i am the fallback title !')
        })
      })
    })
  })
})
