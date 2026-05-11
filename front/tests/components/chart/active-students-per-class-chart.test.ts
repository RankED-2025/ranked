import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { activeStudentsPoints } from '../../mocks/classe'

vi.mock('vue-chartjs', () => ({
  Bar: { name: 'Bar', template: '<div />', props: { data: Object, options: Object } },
}))

import ActiveStudentsPerClassChart from '../../../src/components/chart/ActiveStudentsPerClassChart.vue'

const mountComponent = (points = activeStudentsPoints): VueWrapper =>
  mount(ActiveStudentsPerClassChart, { props: { points } })

// ------------------------------------------------------------------------------

describe('ActiveStudentsPerClassChart', () => {
  let wrapper: VueWrapper

  afterEach(() => {
    wrapper?.unmount()
  })

  describe('Rendering', () => {
    it('should mount without error', () => {
      wrapper = mountComponent()
    })

    it('should render the chart stub', () => {
      wrapper = mountComponent()
      expect(wrapper.findComponent({ name: 'Bar' }).exists()).toBe(true)
    })
  })

  describe('computedData', () => {
    it('should pass class names as labels', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.labels).toEqual(['3A', '3B'])
    })

    it('should pass count values as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.datasets[0].data).toEqual([18, 24])
    })

    it('should set the dataset label to "Élèves actifs"', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.datasets[0].label).toBe('Élèves actifs')
    })
  })

  describe('Reactivity', () => {
    it('should update labels and data when points prop changes', async () => {
      wrapper = mountComponent()

      await wrapper.setProps({
        points: [{ classe: '4C', count: 30 }],
      })

      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.labels).toEqual(['4C'])
      expect(data.datasets[0].data).toEqual([30])
    })
  })

  describe('Edge cases', () => {
    it('should handle an empty points array', () => {
      wrapper = mountComponent([])
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.labels).toEqual([])
      expect(data.datasets[0].data).toEqual([])
    })

    it('should pass chart options with vertical axis starting at zero', () => {
      wrapper = mountComponent()
      const options = wrapper.findComponent({ name: 'Bar' }).props('options')
      expect(options.scales.y.beginAtZero).toBe(true)
    })
  })
})
