import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { badgeDistributionPoints } from '../../mocks/badge.mocks'

vi.mock('vue-chartjs', () => ({
  Pie: { name: 'Pie', template: '<div />', props: { data: Object, options: Object } },
}))

import BadgeDistributionChart from '../../../src/components/chart/BadgeDistributionChart.vue'

const mountComponent = (points = badgeDistributionPoints): VueWrapper =>
  mount(BadgeDistributionChart, { props: { points } })

// ------------------------------------------------------------------------------

describe('BadgeDistributionChart', () => {
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
      expect(wrapper.findComponent({ name: 'Pie' }).exists()).toBe(true)
    })
  })

  describe('computedData', () => {
    it('should pass badge types as labels', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Pie' }).props('data')
      expect(data.labels).toEqual(['bronze', 'or'])
    })

    it('should pass count values as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Pie' }).props('data')
      expect(data.datasets[0].data).toEqual([12, 5])
    })

    it('should set the dataset label to "Badges"', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Pie' }).props('data')
      expect(data.datasets[0].label).toBe('Badges')
    })

    it('should assign badge-specific background colors', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Pie' }).props('data')
      expect(data.datasets[0].backgroundColor).toHaveLength(2)
      expect(data.datasets[0].backgroundColor[0]).toContain('176, 104')  // bronze
      expect(data.datasets[0].backgroundColor[1]).toContain('218, 165')  // or
    })
  })

  describe('Reactivity', () => {
    it('should update labels and data when points prop changes', async () => {
      wrapper = mountComponent()

      await wrapper.setProps({
        points: [{ type: 'platine', count: 2 }],
      })

      const data = wrapper.findComponent({ name: 'Pie' }).props('data')
      expect(data.labels).toEqual(['platine'])
      expect(data.datasets[0].data).toEqual([2])
    })
  })

  describe('Edge cases', () => {
    it('should handle an empty points array', () => {
      wrapper = mountComponent([])
      const data = wrapper.findComponent({ name: 'Pie' }).props('data')
      expect(data.labels).toEqual([])
      expect(data.datasets[0].data).toEqual([])
    })

    it('should use the fallback color for unknown badge types', () => {
      wrapper = mountComponent([{ type: 'unknown-badge', count: 1 }])
      const data = wrapper.findComponent({ name: 'Pie' }).props('data')
      // BADGE_FALLBACK is rgba(46, 60, 136, ...)
      expect(data.datasets[0].backgroundColor[0]).toContain('46, 60, 136')
    })
  })
})
