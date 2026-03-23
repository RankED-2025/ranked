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

// base path of badges
const baseBadgePath: string = '/src/assets/img/badges/'

/**
 * Returns the full path of the badge
 */
const getBadgeImageFullPath = (badge: string): string => {
  if( badge.startsWith('/') ) {
    badge = badge.substring(1)
  }

  let basePath = baseBadgePath

  if( !basePath.startsWith('/') ) {
    basePath = '/' + basePath
  }

  if( !basePath.endsWith('/') ) {
    basePath += '/'
  }

  return basePath + badge
}

// ------------------------------------------------------------------------------

describe('BadgeElement component', () => {
  let wrapper: VueWrapper;

  afterEach(() => {
    wrapper?.unmount()
  })

  describe('Rendering of element', () => {
    it('should mount without any error', () => {
      wrapper = mountComponent()
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

  describe("computed values", () => {
    describe("badgeImagePath", () => {
      it('should match the value in the "src" attribute of the image', () => {
        wrapper = mountComponent({
          props: {
            badgeName: 'or'
          }
        })

        const expectedPath = getBadgeImageFullPath('gold.png')

        expect(wrapper.vm.badgeImagePath).toBe(expectedPath)
        expect(wrapper.get('img.badge-image').element.getAttribute('src'))
          .toBe(expectedPath)
      })

      it.each([
        { name: 'bronze', file: 'bronze.png' },
        { name: 'fer', file: 'silver.png' },
        { name: 'or', file: 'gold.png' },
        { name: 'platine', file: 'platinum.png' },
        { name: 'diamant', file: 'diamond.png' },
      ])('should return the correct image with badgeName=$name prop', ({ name, file }) => {
        wrapper = mountComponent({
          props: {
            badgeName: name
          }
        })

        expect(wrapper.vm.badgeImagePath).toBe(getBadgeImageFullPath(file))
      })

      it('should return the default badge with an unknown value', () => {
        wrapper = mountComponent({
          props: {
            badgeName: 'non-existant-bagde!'
          }
        })

        expect(wrapper.vm.badgeImagePath).toBe(getBadgeImageFullPath('default.png'))
      })

      it('should return the default badge with an empty value', () => {
        wrapper = mountComponent({
          props: {
            badgeName: ''
          }
        })

        expect(wrapper.vm.badgeImagePath).toBe(getBadgeImageFullPath('default.png'))
      })
    })
  })

  describe("reactivity", () => {
    describe("props", () => {
      describe("badgeName", () => {
        it('should change the src of the image accordingly', async () => {
          wrapper = mountComponent({
            props: {
              badgeName: 'or'
            }
          })

          expect(wrapper.get('img.badge-image').element.getAttribute('src'))
            .toBe(getBadgeImageFullPath('gold.png'))

          await wrapper.setProps({
            badgeName: 'diamant'
          })

          expect(wrapper.get('img.badge-image').element.getAttribute('src'))
            .toBe(getBadgeImageFullPath('diamond.png'))
        })

        it('should change the computed value "badgeImagePath" accordingly', async () => {
          wrapper = mountComponent({
            props: {
              badgeName: 'bronze'
            }
          })

          expect(wrapper.vm.badgeImagePath)
            .toBe(getBadgeImageFullPath('bronze.png'))

          await wrapper.setProps({
            badgeName: 'platine'
          })

          expect(wrapper.vm.badgeImagePath)
            .toBe(getBadgeImageFullPath('platinum.png'))
        })
      })
    })
  })

  describe("Edge cases", () => {
    describe("image path", () => {
      it.each([
        { prop: undefined },
        { prop: null }
      ])('should fallback to the default path when the "badgeName" props is $prop', ({ prop }) => {
        wrapper = mountComponent({
          props: {
            badgeName: prop
          }
        })

        expect(wrapper.get('img.badge-image').element.getAttribute('src'))
          .toBe(getBadgeImageFullPath('default.png'))
      })

      it.each([
        { prop: 'OR', expected: 'gold.png' },
        { prop: 'DiaMANt', expected: 'diamond.png' },
        { prop: 'PlAtInE', expected: 'platinum.png' },
        { prop: 'BRONZe', expected: 'bronze.png' },
      ])('should return the correct image path with different capitalisation (given $prop, expected $expected)', ({ prop, expected }) => {
        wrapper = mountComponent({
          props: {
            badgeName: prop
          }
        })

        expect(wrapper.get('img.badge-image').element.getAttribute('src'))
          .toBe(getBadgeImageFullPath(expected))
      })
    })
  })
})
