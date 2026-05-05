import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import type { CompletionBySubjectPoint } from '../../../src/types/component/chart/completion-by-subject'

vi.mock('vue-chartjs', () => ({
  Doughnut: { name: 'Doughnut', template: '<div />', props: { data: Object, options: Object } },
}))

import CompletionBySubjectChart from '../../../src/components/chart/CompletionBySubjectChart.vue'

const defaultPoints: CompletionBySubjectPoint[] = [
  { subject: 'Maths', average: 78.5 },
  { subject: 'Histoire', average: 63.0 },
]

const mountComponent = (points = defaultPoints): VueWrapper =>
  mount(CompletionBySubjectChart, { props: { points } })

// ------------------------------------------------------------------------------

describe('CompletionBySubjectChart', () => {
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
      expect(wrapper.findComponent({ name: 'Doughnut' }).exists()).toBe(true)
    })
  })

  describe('computedData', () => {
    it('should pass subject names as labels', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Doughnut' }).props('data')
      expect(data.labels).toEqual(['Maths', 'Histoire'])
    })

    it('should pass average values as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Doughnut' }).props('data')
      expect(data.datasets[0].data).toEqual([78.5, 63.0])
    })

    it('should set the dataset label to "Complétion moy. (%)"', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Doughnut' }).props('data')
      expect(data.datasets[0].label).toBe('Complétion moy. (%)')
    })
  })

  describe('Reactivity', () => {
    it('should update labels and data when points prop changes', async () => {
      wrapper = mountComponent()

      await wrapper.setProps({
        points: [{ subject: 'Sciences', average: 90 }],
      })

      const data = wrapper.findComponent({ name: 'Doughnut' }).props('data')
      expect(data.labels).toEqual(['Sciences'])
      expect(data.datasets[0].data).toEqual([90])
    })
  })

  describe('Edge cases', () => {
    it('should handle an empty points array', () => {
      wrapper = mountComponent([])
      const data = wrapper.findComponent({ name: 'Doughnut' }).props('data')
      expect(data.labels).toEqual([])
      expect(data.datasets[0].data).toEqual([])
    })
  })
})
