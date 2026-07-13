import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import { vi, afterEach, beforeEach, describe, it, expect } from 'vitest'
import { getByTestId, vuetifyInstance } from '../../util/vuetify-utils'
import { mockMatieres, mockDifficultes, mockCreatedCourse } from '../../mocks/cours'

// ── Hoisted mocks ────────────────────────────────────────────────────────────
const { mockCourseService, mockReferentielService, mockRouter } = vi.hoisted(() => ({
  mockCourseService: {
    createCourse: vi.fn(),
  },
  mockReferentielService: {
    getMatieres: vi.fn(),
    getDifficultes: vi.fn(),
  },
  mockRouter: { push: vi.fn() },
}))

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...(actual as object),
    useRouter: () => mockRouter,
  }
})

vi.mock('@/services/courseService', () => ({
  courseService: mockCourseService,
}))

vi.mock('@/services/referentielService', () => ({
  referentielService: mockReferentielService,
}))

// ── Import after mocks ────────────────────────────────────────────────────────
import CreateCourseForm from '../../../src/components/professor/CreateCourseForm.vue'

const mountComponent = (): VueWrapper =>
  mount(CreateCourseForm, {
    global: { plugins: [vuetifyInstance] },
  })

const fillAndSubmit = async (wrapper: VueWrapper) => {
  await flushPromises()

  await wrapper.get(getByTestId('title-field')).find('input').setValue('Mon nouveau cours')
  await wrapper.get(getByTestId('description-field')).find('textarea').setValue('Une description')
  await wrapper.findComponent(getByTestId('matiere-select')).setValue(1)
  await wrapper.findComponent(getByTestId('difficulte-select')).setValue(2)
  await flushPromises()

  await wrapper.get(getByTestId('create-course-form')).trigger('submit')
  await flushPromises()
}

describe('CreateCourseForm', () => {
  let wrapper: VueWrapper

  beforeEach(() => {
    vi.useFakeTimers()
    mockReferentielService.getMatieres.mockResolvedValue(mockMatieres)
    mockReferentielService.getDifficultes.mockResolvedValue(mockDifficultes)
    mockCourseService.createCourse.mockResolvedValue(mockCreatedCourse)
  })

  afterEach(() => {
    wrapper?.unmount()
    vi.clearAllMocks()
    vi.useRealTimers()
  })

  describe('after a successful creation', () => {
    beforeEach(async () => {
      wrapper = mountComponent()
      await fillAndSubmit(wrapper)
    })

    it('creates the course with the form data', () => {
      expect(mockCourseService.createCourse).toHaveBeenCalledWith({
        title: 'Mon nouveau cours',
        description: 'Une description',
        matiere_id: 1,
        difficulte_id: 2,
      })
    })

    it('redirects to the edit page of the created course instead of the home page', async () => {
      vi.advanceTimersByTime(1500)
      await flushPromises()

      expect(mockRouter.push).toHaveBeenCalledWith('/professor/edit-course/42')
      expect(mockRouter.push).not.toHaveBeenCalledWith('/')
    })
  })

  it('does not navigate away before the success delay has elapsed', async () => {
    wrapper = mountComponent()
    await fillAndSubmit(wrapper)

    expect(mockRouter.push).not.toHaveBeenCalled()
  })

  it('does not navigate when the creation request fails', async () => {
    mockCourseService.createCourse.mockRejectedValue(new Error('boom'))

    wrapper = mountComponent()
    await fillAndSubmit(wrapper)

    vi.advanceTimersByTime(1500)
    await flushPromises()

    expect(mockRouter.push).not.toHaveBeenCalled()
  })
})
