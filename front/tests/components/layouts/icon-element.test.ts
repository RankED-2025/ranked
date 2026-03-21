import IconElement from '../../../src/components/layouts/IconElement.vue'
import { mount, VueWrapper } from '@vue/test-utils'
import { describe } from 'vitest'

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

})
