import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import type { MyClassRank } from '../../../src/types/component/chart/my-class-rank'
import { myClassRankDefault } from '../../mocks/user'

vi.mock('vue-chartjs', () => ({
  Bar: { name: 'Bar', template: '<div />', props: { data: Object, options: Object } },
  Line: { name: 'Line', template: '<div />', props: { data: Object, options: Object } },
  Doughnut: { name: 'Doughnut', template: '<div />', props: { data: Object, options: Object } },
  Pie: { name: 'Pie', template: '<div />', props: { data: Object, options: Object } },
  Radar: { name: 'Radar', template: '<div />', props: { data: Object, options: Object } },
}))

import MyClassRankChart from '../../../src/components/chart/MyClassRankChart.vue'

const mountComponent = (rank = myClassRankDefault): VueWrapper =>
  mount(MyClassRankChart, { props: { rank } })

// ------------------------------------------------------------------------------

describe('MyClassRankChart', () => {
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

    it('should render the rank label paragraph', () => {
      wrapper = mountComponent()
      expect(wrapper.find('p.rank-label').exists()).toBe(true)
    })
  })

  describe('computedData', () => {
    it('should pass "Mon percentile" as the only label', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.labels).toEqual(['Mon percentile'])
    })

    it('should pass the percentile value as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.datasets[0].data).toEqual([88])
    })

    it('should set the dataset label to "Percentile"', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.datasets[0].label).toBe('Percentile')
    })
  })

  describe('Rank label text', () => {
    it('should display rank, total and average in the label', () => {
      wrapper = mountComponent()
      const text = wrapper.find('p.rank-label').text()
      expect(text).toContain('3')
      expect(text).toContain('25')
      expect(text).toContain('78')
    })

    it.each([
      { rank: 1, total: 30, myAverage: 95, percentile: 97 },
      { rank: 15, total: 20, myAverage: 50, percentile: 25 },
    ])('should reflect rank=$rank total=$total avg=$myAverage', (rankData) => {
      wrapper = mountComponent(rankData)
      const text = wrapper.find('p.rank-label').text()
      expect(text).toContain(String(rankData.rank))
      expect(text).toContain(String(rankData.total))
      expect(text).toContain(String(rankData.myAverage))
    })
  })

  describe('Reactivity', () => {
    it('should update chart data and label when rank prop changes', async () => {
      wrapper = mountComponent()

      const newRank: MyClassRank = { rank: 1, total: 25, myAverage: 98, percentile: 96 }
      await wrapper.setProps({ rank: newRank })

      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.datasets[0].data).toEqual([96])
      expect(wrapper.find('p.rank-label').text()).toContain('1')
      expect(wrapper.find('p.rank-label').text()).toContain('98')
    })
  })

  describe('Chart options', () => {
    it('should pass chart options with indexAxis "y" and x-axis max 100', () => {
      wrapper = mountComponent()
      const options = wrapper.findComponent({ name: 'Bar' }).props('options')
      expect(options.indexAxis).toBe('y')
      expect(options.scales.x.max).toBe(100)
    })
  })
})
