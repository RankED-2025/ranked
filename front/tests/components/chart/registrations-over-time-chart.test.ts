import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import type { RegistrationsOverTimePoint } from '../../../src/types/component/chart/registrations-over-time'

vi.mock('vue-chartjs', () => ({
  Line: { name: 'Line', template: '<div />', props: { data: Object, options: Object } },
}))

import RegistrationsOverTimeChart from '../../../src/components/chart/RegistrationsOverTimeChart.vue'

const defaultPoints: RegistrationsOverTimePoint[] = [
  { week: '2024-W01', count: 10 },
  { week: '2024-W02', count: 15 },
  { week: '2024-W03', count: 8 },
]

const mountComponent = (points = defaultPoints): VueWrapper =>
  mount(RegistrationsOverTimeChart, { props: { points } })

// ------------------------------------------------------------------------------

describe('RegistrationsOverTimeChart', () => {
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
      expect(wrapper.findComponent({ name: 'Line' }).exists()).toBe(true)
    })
  })

  describe('computedData', () => {
    it('should pass week values as labels', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.labels).toEqual(['2024-W01', '2024-W02', '2024-W03'])
    })

    it('should pass count values as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.datasets[0].data).toEqual([10, 15, 8])
    })

    it('should set the dataset label to "Nouvelles inscriptions"', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.datasets[0].label).toBe('Nouvelles inscriptions')
    })

    it('should set fill to false and tension to 0.3', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.datasets[0].fill).toBe(false)
      expect(data.datasets[0].tension).toBe(0.3)
    })
  })

  describe('Reactivity', () => {
    it('should update labels and data when points prop changes', async () => {
      wrapper = mountComponent()

      await wrapper.setProps({
        points: [{ week: '2024-W10', count: 20 }],
      })

      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.labels).toEqual(['2024-W10'])
      expect(data.datasets[0].data).toEqual([20])
    })
  })

  describe('Edge cases', () => {
    it('should handle an empty points array', () => {
      wrapper = mountComponent([])
      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.labels).toEqual([])
      expect(data.datasets[0].data).toEqual([])
    })
  })
})
