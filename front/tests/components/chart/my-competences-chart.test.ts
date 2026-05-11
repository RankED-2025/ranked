import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { myCompetencesPoints } from '../../mocks/competence.mocks'

vi.mock('vue-chartjs', () => ({
  Radar: { name: 'Radar', template: '<div />', props: { data: Object, options: Object } },
}))

import MyCompetencesChart from '../../../src/components/chart/MyCompetencesChart.vue'

const mountComponent = (points = myCompetencesPoints): VueWrapper =>
  mount(MyCompetencesChart, { props: { points } })

// ------------------------------------------------------------------------------

describe('MyCompetencesChart', () => {
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
      expect(wrapper.findComponent({ name: 'Radar' }).exists()).toBe(true)
    })
  })

  describe('computedData', () => {
    it('should pass subject names as labels', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Radar' }).props('data')
      expect(data.labels).toEqual(['Maths', 'Physique', 'Chimie'])
    })

    it('should pass percentage values as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Radar' }).props('data')
      expect(data.datasets[0].data).toEqual([80, 65, 90])
    })

    it('should set the dataset label to "Compétences acquises (%)"', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Radar' }).props('data')
      expect(data.datasets[0].label).toBe('Compétences acquises (%)')
    })

    it('should set fill to true', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Radar' }).props('data')
      expect(data.datasets[0].fill).toBe(true)
    })
  })

  describe('Reactivity', () => {
    it('should update labels and data when points prop changes', async () => {
      wrapper = mountComponent()

      await wrapper.setProps({
        points: [{ matiere: 'Histoire', percentage: 70 }],
      })

      const data = wrapper.findComponent({ name: 'Radar' }).props('data')
      expect(data.labels).toEqual(['Histoire'])
      expect(data.datasets[0].data).toEqual([70])
    })
  })

  describe('Edge cases', () => {
    it('should handle an empty points array', () => {
      wrapper = mountComponent([])
      const data = wrapper.findComponent({ name: 'Radar' }).props('data')
      expect(data.labels).toEqual([])
      expect(data.datasets[0].data).toEqual([])
    })

    it('should pass radar scale options with min 0 and max 100', () => {
      wrapper = mountComponent()
      const options = wrapper.findComponent({ name: 'Radar' }).props('options')
      expect(options.scales.r.min).toBe(0)
      expect(options.scales.r.max).toBe(100)
    })
  })
})
