import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import type { MyQuizScorePoint } from '../../../src/types/component/chart/my-quiz-scores'

vi.mock('vue-chartjs', () => ({
  Line: { name: 'Line', template: '<div />', props: { data: Object, options: Object } },
}))

import MyQuizScoresChart from '../../../src/components/chart/MyQuizScoresChart.vue'

const defaultPoints: MyQuizScorePoint[] = [
  { label: 'Quiz 1', points: 18 },
  { label: 'Quiz 2', points: 14 },
  { label: 'Quiz 3', points: 20 },
]

const mountComponent = (points = defaultPoints): VueWrapper =>
  mount(MyQuizScoresChart, { props: { points } })

// ------------------------------------------------------------------------------

describe('MyQuizScoresChart', () => {
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
    it('should pass quiz labels as labels', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.labels).toEqual(['Quiz 1', 'Quiz 2', 'Quiz 3'])
    })

    it('should pass points values as dataset data', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.datasets[0].data).toEqual([18, 14, 20])
    })

    it('should set the dataset label to "Points"', () => {
      wrapper = mountComponent()
      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.datasets[0].label).toBe('Points')
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
        points: [{ label: 'Quiz Final', points: 19 }],
      })

      const data = wrapper.findComponent({ name: 'Line' }).props('data')
      expect(data.labels).toEqual(['Quiz Final'])
      expect(data.datasets[0].data).toEqual([19])
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
