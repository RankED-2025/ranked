import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { myProgressionPoints } from '../../mocks/progression'

vi.mock('vue-chartjs', () => ({
  Bar: { name: 'Bar', template: '<div />', props: { data: Object, options: Object } },
}))

import MyProgressionChart from '../../../src/components/chart/MyProgressionChart.vue'

const mountComponent = (points = myProgressionPoints): VueWrapper =>
  mount(MyProgressionChart, { props: { points } })

// ------------------------------------------------------------------------------

describe('MyProgressionChart', () => {
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
    it('should pass course titles as labels', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.labels).toEqual(['Introduction à Python', 'Algèbre linéaire'])
    })

    it('should pass percentage values as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.datasets[0].data).toEqual([100, 55])
    })

    it('should set the dataset label to "Complétion (%)"', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.datasets[0].label).toBe('Complétion (%)')
    })
  })

  describe('Reactivity', () => {
    it('should update labels and data when points prop changes', async () => {
      wrapper = mountComponent()

      await wrapper.setProps({
        points: [{ title: 'Chimie organique', percentage: 30 }],
      })

      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.labels).toEqual(['Chimie organique'])
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

    it('should pass chart options with indexAxis "y" and x-axis max 100', () => {
      wrapper = mountComponent()
      const options = wrapper.findComponent({ name: 'Bar' }).props('options')
      expect(options.indexAxis).toBe('y')
      expect(options.scales.x.max).toBe(100)
    })
  })
})
