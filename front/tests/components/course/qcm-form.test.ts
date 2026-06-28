import { vi, afterEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import { getByTestId, globalTestPlugins } from '../../util/vuetify-utils'
import type { QuizToTake } from '../../../src/types'

// ── Hoisted service mock ────────────────────────────────────────────────────
const { mockCourseService } = vi.hoisted(() => ({
  mockCourseService: {
    getQuiz: vi.fn(),
    submitQuiz: vi.fn(),
  },
}))

vi.mock('../../../src/services/courseService.ts', () => ({
  courseService: mockCourseService,
}))

vi.mock('@/services/courseService', () => ({
  courseService: mockCourseService,
}))

// ── Import after mocks ──────────────────────────────────────────────────────
import QcmForm from '../../../src/components/course/QcmForm.vue'

// ── Mock data ───────────────────────────────────────────────────────────────
const openQuiz: QuizToTake = {
  id: 7,
  gainPts: 20,
  locked: false,
  questions: [
    {
      id: 1,
      enonce: 'Capitale de la France ?',
      reponses: [
        { id: 11, texte: 'Paris' },
        { id: 12, texte: 'Lyon' },
      ],
    },
    {
      id: 2,
      enonce: '2 + 2 ?',
      reponses: [
        { id: 21, texte: '4' },
        { id: 22, texte: '5' },
      ],
    },
  ],
}

const mountComponent = (activityId = 5): VueWrapper =>
  mount(QcmForm, {
    props: { activityId },
    global: { plugins: globalTestPlugins },
  })

afterEach(() => {
  vi.clearAllMocks()
})

describe('QcmForm', () => {
  it('renders the questions when the quiz is not locked', async () => {
    mockCourseService.getQuiz.mockResolvedValue(openQuiz)

    const wrapper = mountComponent()
    await flushPromises()

    expect(mockCourseService.getQuiz).toHaveBeenCalledWith(5)
    expect(wrapper.findAll(getByTestId('question-0'))).toHaveLength(1)
    expect(wrapper.findAll('.question')).toHaveLength(2)
    expect(wrapper.find(getByTestId('qcm-submit')).attributes('disabled')).toBeDefined()
  })

  it('enables submit once every question is answered and submits the answers', async () => {
    mockCourseService.getQuiz.mockResolvedValue(openQuiz)
    mockCourseService.submitQuiz.mockResolvedValue({ score: 2, total: 2, earnedPts: 20, gainPts: 20 })

    const wrapper = mountComponent(5)
    await flushPromises()

    const radios = wrapper.findAll('input[type="radio"]')
    await radios[0].setValue() // question 1 -> reponse 11
    await radios[2].setValue() // question 2 -> reponse 21

    expect(wrapper.find(getByTestId('qcm-submit')).attributes('disabled')).toBeUndefined()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(mockCourseService.submitQuiz).toHaveBeenCalledWith(5, { 1: 11, 2: 21 })
    expect(wrapper.emitted('completed')).toBeTruthy()
    expect(wrapper.emitted('completed')![0]).toEqual([5])

    const result = wrapper.find(getByTestId('qcm-result'))
    expect(result.exists()).toBe(true)
    expect(result.text()).toContain('2 / 2')
  })

  it('shows the locked result without a form when already attempted', async () => {
    mockCourseService.getQuiz.mockResolvedValue({
      id: 7,
      gainPts: 20,
      locked: true,
      result: { score: 1, total: 2, earnedPts: 10 },
    } satisfies QuizToTake)

    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.find(getByTestId('qcm-result')).exists()).toBe(true)
    expect(wrapper.find('form').exists()).toBe(false)
    expect(wrapper.find(getByTestId('qcm-result')).text()).toContain('1 / 2')
  })

  it('shows an error message when the quiz cannot be loaded', async () => {
    mockCourseService.getQuiz.mockRejectedValue({ response: { data: { error: 'Boom' } } })

    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.find(getByTestId('qcm-error')).text()).toBe('Boom')
  })

  it('shows a submit error when submission fails', async () => {
    mockCourseService.getQuiz.mockResolvedValue(openQuiz)
    mockCourseService.submitQuiz.mockRejectedValue({ response: { data: { error: 'Already submitted' } } })

    const wrapper = mountComponent()
    await flushPromises()

    const radios = wrapper.findAll('input[type="radio"]')
    await radios[0].setValue()
    await radios[2].setValue()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(wrapper.find(getByTestId('qcm-submit-error')).text()).toBe('Already submitted')
    expect(wrapper.emitted('completed')).toBeFalsy()
  })
})
