import { vi, afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'
import { vuetifyInstance } from '../util/vuetify-utils'
import { defaultStatusMessageCases } from '../util/status-messages'
import type { ClassSummary } from '../../src/types'

// ── Hoisted mocks ───────────────────────────────────────────────────────────
const { mockCourseService, mockRouter } = vi.hoisted(() => ({
  mockCourseService: {
    getProfessorClasses: vi.fn(),
  },
  mockRouter: { push: vi.fn(), back: vi.fn() },
}))

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...(actual as object),
    useRouter: () => mockRouter,
  }
})

vi.mock('../../src/services/courseService.ts', () => ({
  courseService: mockCourseService,
}))

// ── Import after mocks ────────────────────────────────────────────────────────
import ProfessorClassesView from '../../src/views/Professor/ProfessorClassesView.vue'

// ── Mock data ─────────────────────────────────────────────────────────────────
const classA: ClassSummary = {
  id: 1,
  nom: '5ème B',
  studentCount: 24,
  courseCount: 3,
  averagePercentage: 68,
  studentsAtLeast50: 16,
  studentsAt100: 2,
}

const classNoCourse: ClassSummary = {
  id: 2,
  nom: '4ème A',
  studentCount: 22,
  courseCount: 0,
  averagePercentage: null,
  studentsAtLeast50: 0,
  studentsAt100: 0,
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const mountView = (): VueWrapper =>
  mount(ProfessorClassesView, { global: { plugins: [vuetifyInstance] } })

// ── Tests ─────────────────────────────────────────────────────────────────────
describe('ProfessorClassesView', () => {
  let wrapper: VueWrapper

  beforeEach(() => {
    mockCourseService.getProfessorClasses.mockResolvedValue([classA])
  })

  afterEach(() => {
    wrapper?.unmount()
    vi.clearAllMocks()
  })

  // ── Rendering ─────────────────────────────────────────────────────────────
  describe('Rendering', () => {
    it('should mount without error', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.exists()).toBe(true)
    })

    it('should call getProfessorClasses on mount', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(mockCourseService.getProfessorClasses).toHaveBeenCalledOnce()
    })

    it('should show skeleton loaders while fetching', async () => {
      mockCourseService.getProfessorClasses.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await nextTick()
      expect(wrapper.findAll('.v-skeleton-loader').length).toBeGreaterThan(0)
    })

    it('should hide skeleton loaders once loaded', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.findAll('.v-skeleton-loader').length).toBe(0)
    })

    describe.each(
      defaultStatusMessageCases()
    )('when getProfessorClasses rejects with status $status', ({ status, message }) => {
      it('shows the default error message', async () => {
        mockCourseService.getProfessorClasses.mockRejectedValue({ response: { status } })
        wrapper = mountView()
        await flushPromises()
        expect(wrapper.text()).toContain(message)
      })
    })

    it('should show the empty state when there are no classes', async () => {
      mockCourseService.getProfessorClasses.mockResolvedValue([])
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Aucune classe pour le moment')
    })
  })

  // ── Class card ────────────────────────────────────────────────────────────
  describe('Class card', () => {
    it('should display the class name', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('5ème B')
    })

    it('should display the student count (plural)', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('24 élèves')
    })

    it('should display "élève" (singular) for a single student', async () => {
      mockCourseService.getProfessorClasses.mockResolvedValue([{ ...classA, studentCount: 1 }])
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('1 élève')
      expect(wrapper.text()).not.toContain('1 élèves')
    })

    it('should display the course count and completion stats when courses are assigned', async () => {
      wrapper = mountView()
      await flushPromises()
      const text = wrapper.text()
      expect(text).toContain('Cours')
      expect(text).toContain('≥ 50%')
      expect(text).toContain('100%')
    })

    it('should display the average percentage', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('68%')
    })

    it('should navigate to the class detail page when clicked', async () => {
      wrapper = mountView()
      await flushPromises()
      await wrapper.find('.class-card').trigger('click')
      expect(mockRouter.push).toHaveBeenCalledWith('/professor/classes/1')
    })
  })

  // ── Class card with no courses assigned ──────────────────────────────────
  describe('Class card with no courses assigned', () => {
    beforeEach(() => {
      mockCourseService.getProfessorClasses.mockResolvedValue([classNoCourse])
    })

    it('should show the "no course assigned" note instead of stats', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Aucun cours assigné')
    })

    it('should show a "—" in the progress ring instead of a percentage', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find('.ring-pct').text()).toBe('—')
    })

    it('should show an "Assigner un cours" call to action', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Assigner un cours')
    })

    it('should navigate to the assign-course page (not the class detail) when the CTA is clicked', async () => {
      wrapper = mountView()
      await flushPromises()
      await wrapper.find('.class-card-cta').trigger('click')
      expect(mockRouter.push).toHaveBeenCalledWith('/professor/assign-course')
      expect(mockRouter.push).not.toHaveBeenCalledWith('/professor/classes/2')
    })
  })
})
