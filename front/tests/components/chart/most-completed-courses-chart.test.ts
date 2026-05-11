import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { makeCourse, mostCompletedCoursesPoints } from '../../mocks/cours'

vi.mock('vue-chartjs', () => ({
  Bar: { name: 'Bar', template: '<div />', props: { data: Object, options: Object } },
}))

import MostCompletedCoursesChart from '../../../src/components/chart/MostCompletedCoursesChart.vue'

const mountComponent = (points = mostCompletedCoursesPoints): VueWrapper =>
  mount(MostCompletedCoursesChart, { props: { points } })

// ------------------------------------------------------------------------------

describe('MostCompletedCoursesChart', () => {
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
      expect(data.labels).toEqual(['Algèbre', 'Géométrie'])
    })

    it('should pass percent values as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.datasets[0].data).toEqual([85, 72])
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
        points: [{ percent: 60, course: makeCourse('Physique') }],
      })

      const data = wrapper.findComponent({ name: 'Bar' }).props('data')
      expect(data.labels).toEqual(['Physique'])
      expect(data.datasets[0].data).toEqual([60])
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
