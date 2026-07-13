import { vi, afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'
import { vuetifyInstance, getByTestId } from '../util/vuetify-utils'
import { defaultStatusMessageCases } from '../util/status-messages'
import {
  classDetail,
  classDetailMultiStudents,
  classDetailNullCours,
  classDetailNullPercentage,
  classDetailSilverBadge,
  classDetailBronzeBadge,
  classDetailBothStudentsWithProgressions,
  classCourses,
  classCoursesNoProgressions,
} from '../mocks/classe'

// ── Test-specific types ───────────────────────────────────────────────────────────
type StudentCell = {
  courseId: number
  percentage: number | null
  badge: { id: number; type: string; label: string } | null
}

type StudentRow = {
  id: number
  name: string
  firstname: string
  average: number | null
  cells: StudentCell[]
}

type VmOfComponent = {
  loading: boolean
  classId: number
  progressColor: (pct: number | null) => string
  studentRows: StudentRow[]
  classAverage: number | null
}

// ── Hoisted mocks ───────────────────────────────────────────────────────────
const { mockCourseService, mockRoute, mockRouter } = vi.hoisted(() => ({
  mockCourseService: {
    getProfessorClassDetail: vi.fn(),
    getProfessorClassCourses: vi.fn(),
  },
  mockRoute: { params: { id: '42' } },
  mockRouter: { back: vi.fn(), push: vi.fn() },
}))

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...(actual as object),
    useRoute: () => mockRoute,
    useRouter: () => mockRouter,
  }
})

vi.mock('../../src/services/courseService.ts', () => ({
  courseService: mockCourseService,
}))

vi.mock('@/components/professor/BestStudentsCard.vue', () => ({
  default: {
    name: 'BestStudentsCard',
    template: '<div data-testid="best-students-card" />',
    props: { classeId: Number, limit: Number },
  },
}))

// ── Import after mocks ──────────────────────────────────────────────────────
import ProfessorClassDetailView from '../../src/views/Professor/ProfessorClassDetailView.vue'

// ── Stubs ─────────────────────────────────────────────────────────────────────
const stubs = {
  VContainer: { template: '<div><slot /></div>' },
  VProgressCircular: { template: '<div class="v-progress-circular" />' },
  VAlert: { template: '<div class="v-alert"><slot /></div>', props: ['type'] },
  VBtn: { template: '<button><slot /></button>' },
  VIcon: { template: '<span><slot /></span>' },
}

// ── Helpers ──────────────────────────────────────────────────────────────────
const mountView = (): VueWrapper =>
  mount(ProfessorClassDetailView, { global: { plugins: [vuetifyInstance], stubs } })

// ── Tests ─────────────────────────────────────────────────────────────────────
describe('ProfessorClassDetailView', () => {
  let wrapper: VueWrapper

  beforeEach(() => {
    mockRoute.params.id = '42'
    mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetail)
    mockCourseService.getProfessorClassCourses.mockResolvedValue(classCourses)
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

    it('should call getProfessorClassDetail with the parsed class id', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(mockCourseService.getProfessorClassDetail).toHaveBeenCalledWith(42)
    })

    it('should call getProfessorClassCourses with the parsed class id', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(mockCourseService.getProfessorClassCourses).toHaveBeenCalledWith(42)
    })

    it('should call both API endpoints in parallel on mount', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(mockCourseService.getProfessorClassDetail).toHaveBeenCalledOnce()
      expect(mockCourseService.getProfessorClassCourses).toHaveBeenCalledOnce()
    })

    it('should display the class name after loading', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Terminale A')
    })

    it('should show "Classe" while loading (classDetail is null)', async () => {
      mockCourseService.getProfessorClassDetail.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await nextTick()
      expect(wrapper.text()).toContain('Classe')
    })

    it('should show only the loading spinner while fetching', async () => {
      mockCourseService.getProfessorClassDetail.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await nextTick()
      expect(wrapper.find(getByTestId('loading-spinner')).exists()).toBe(true)
      expect(wrapper.find(getByTestId('error-alert')).exists()).toBe(false)
      expect(wrapper.find(getByTestId('best-students-card')).exists()).toBe(false)
    })

    // ProfessorClassDetailView passes no overrides to StatusAlert, so every status must show
    // the shared DEFAULT_STATUS_MESSAGES message — generated from it directly.
    describe.each(
      defaultStatusMessageCases()
    )('when getProfessorClassDetail rejects with status $status', ({ status, message }) => {
      it('shows only the default error alert', async () => {
        mockCourseService.getProfessorClassDetail.mockRejectedValue({ response: { status } })
        wrapper = mountView()
        await flushPromises()
        expect(wrapper.find(getByTestId('error-alert')).exists()).toBe(true)
        expect(wrapper.text()).toContain(message)
        expect(wrapper.find(getByTestId('loading-spinner')).exists()).toBe(false)
        expect(wrapper.find(getByTestId('best-students-card')).exists()).toBe(false)
      })
    })

    describe.each(
      defaultStatusMessageCases()
    )('when getProfessorClassCourses rejects with status $status', ({ status, message }) => {
      it('shows only the default error alert', async () => {
        mockCourseService.getProfessorClassCourses.mockRejectedValue({ response: { status } })
        wrapper = mountView()
        await flushPromises()
        expect(wrapper.find(getByTestId('error-alert')).exists()).toBe(true)
        expect(wrapper.text()).toContain(message)
        expect(wrapper.find(getByTestId('loading-spinner')).exists()).toBe(false)
      })
    })

    it('should show only the error alert when the route param is not a valid number', async () => {
      mockRoute.params.id = 'invalid'
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('error-alert')).exists()).toBe(true)
      expect(wrapper.text()).toContain('Identifiant de classe invalide.')
      expect(wrapper.find(getByTestId('loading-spinner')).exists()).toBe(false)
      expect(wrapper.find(getByTestId('best-students-card')).exists()).toBe(false)
      expect(mockCourseService.getProfessorClassDetail).not.toHaveBeenCalled()
      expect(mockCourseService.getProfessorClassCourses).not.toHaveBeenCalled()
    })

    it('should call router.back() when the back button is clicked', async () => {
      wrapper = mountView()
      await flushPromises()
      await wrapper.find(getByTestId('back-button')).trigger('click')
      expect(mockRouter.back).toHaveBeenCalled()
    })
  })

  // ── classId computed ───────────────────────────────────────────────────────
  describe('classId computed', () => {
    it('should return the parsed id when route param is numeric', async () => {
      mockRoute.params.id = '99'
      wrapper = mountView()
      await flushPromises()
      expect((wrapper.vm as unknown as VmOfComponent).classId).toBe(99)
    })

    it('should return null when route param is a non-numeric string', async () => {
      mockRoute.params.id = 'abc'
      wrapper = mountView()
      await flushPromises()
      expect((wrapper.vm as unknown as VmOfComponent).classId).toBeNull()
    })

    it('should return null when route param is undefined', async () => {
      mockRoute.params.id = undefined
      wrapper = mountView()
      await flushPromises()
      expect((wrapper.vm as unknown as VmOfComponent).classId).toBeNull()
    })
  })

  // ── progressColor ──────────────────────────────────────────────────────────
  describe('progressColor', () => {
    beforeEach(async () => {
      wrapper = mountView()
      await flushPromises()
    })

    it('should return "grey" for null percentage', () => {
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(null)).toBe('grey')
    })

    it('should return "success" for percentage >= 100', () => {
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(100)).toBe('success')
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(110)).toBe('success')
    })

    it('should return "warning" for 50 <= percentage < 100', () => {
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(50)).toBe('warning')
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(75)).toBe('warning')
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(99)).toBe('warning')
    })

    it('should return "error" for percentage < 50', () => {
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(49)).toBe('error')
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(0)).toBe('error')
    })
  })

  // ── studentRows computed (one row per student, one cell per assigned course) ──
  describe('studentRows computed', () => {
    it('should return [] when classDetail is null', async () => {
      mockCourseService.getProfessorClassDetail.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await nextTick()
      expect((wrapper.vm as unknown as VmOfComponent).studentRows).toEqual([])
    })

    it('should map each student to a cell per assigned course', async () => {
      wrapper = mountView()
      await flushPromises()
      const rows = (wrapper.vm as unknown as VmOfComponent).studentRows
      expect(rows).toHaveLength(1)
      expect(rows[0]).toMatchObject({ id: 1, name: 'Martin', firstname: 'Alice', average: 80 })
      expect(rows[0].cells).toEqual([{ courseId: 10, percentage: 80, badge: null }])
    })

    it('should skip progressions where cours is null', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNullCours)
      mockCourseService.getProfessorClassCourses.mockResolvedValue([])
      wrapper = mountView()
      await flushPromises()
      const rows = (wrapper.vm as unknown as VmOfComponent).studentRows
      expect(rows).toHaveLength(1)
      expect(rows[0].cells).toEqual([])
      expect(rows[0].average).toBeNull()
    })

    it('should set percentage to null when prog.percentage is undefined', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNullPercentage)
      wrapper = mountView()
      await flushPromises()
      const rows = (wrapper.vm as unknown as VmOfComponent).studentRows
      expect(rows[0].cells[0].percentage).toBeNull()
      expect(rows[0].average).toBeNull()
    })

    it('should give a student without a progression a null cell for that course', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      const rows = (wrapper.vm as unknown as VmOfComponent).studentRows
      const bob = rows.find((s) => s.id === 2)
      expect(bob).toBeDefined()
      expect(bob!.cells).toEqual([{ courseId: 10, percentage: null, badge: null }])
      expect(bob!.average).toBeNull()
    })

    it('should include every assigned course as a cell for every student', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      const rows = (wrapper.vm as unknown as VmOfComponent).studentRows
      expect(rows).toHaveLength(2)
      rows.forEach((row) => expect(row.cells).toHaveLength(classCourses.length))
    })

    it('should give each student their own percentage for a shared course', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailBothStudentsWithProgressions)
      wrapper = mountView()
      await flushPromises()
      const rows = (wrapper.vm as unknown as VmOfComponent).studentRows
      expect(rows.find((s) => s.id === 1)?.cells[0].percentage).toBe(95)
      expect(rows.find((s) => s.id === 2)?.cells[0].percentage).toBe(60)
    })

    it('should give every student a null cell for a course that has no progressions yet', async () => {
      mockCourseService.getProfessorClassCourses.mockResolvedValue(classCoursesNoProgressions)
      wrapper = mountView()
      await flushPromises()
      const rows = (wrapper.vm as unknown as VmOfComponent).studentRows
      expect(rows[0].cells).toEqual([{ courseId: 99, percentage: null, badge: null }])
    })
  })

  // ── classAverage computed ──────────────────────────────────────────────────
  describe('classAverage computed', () => {
    it('should be null when no student has an average yet', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNullCours)
      mockCourseService.getProfessorClassCourses.mockResolvedValue([])
      wrapper = mountView()
      await flushPromises()
      expect((wrapper.vm as unknown as VmOfComponent).classAverage).toBeNull()
    })

    it('should average the per-student averages, ignoring students with no data', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailBothStudentsWithProgressions)
      wrapper = mountView()
      await flushPromises()
      // (95 + 60) / 2 = 77.5 -> rounds to 78
      expect((wrapper.vm as unknown as VmOfComponent).classAverage).toBe(78)
    })
  })

  // ── BestStudentsCard integration ───────────────────────────────────────────
  describe('BestStudentsCard integration', () => {
    it('should render BestStudentsCard when classId is valid and classDetail is loaded', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('best-students-card')).exists()).toBe(true)
    })

    it('should pass classId as classeId prop to BestStudentsCard', async () => {
      wrapper = mountView()
      await flushPromises()
      const card = wrapper.findComponent({ name: 'BestStudentsCard' })
      expect(card.props('classeId')).toBe(42)
    })

    it('should not render BestStudentsCard when classId is null', async () => {
      mockRoute.params.id = 'not-a-number'
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('best-students-card')).exists()).toBe(false)
    })

    it('should not render BestStudentsCard while classDetail is loading', () => {
      mockCourseService.getProfessorClassDetail.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      expect(wrapper.find(getByTestId('best-students-card')).exists()).toBe(false)
    })
  })

  // ── No courses state ───────────────────────────────────────────────────────
  describe('No courses state', () => {
    beforeEach(() => {
      mockCourseService.getProfessorClassCourses.mockResolvedValue([])
    })

    it('should show the "no courses" card when assignedCourses is empty', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Aucun cours assigné à cette classe')
    })

    it('should show the "assign course" button when no courses', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Assigner un cours')
    })

    it('should navigate to /professor/assign-course when the button is clicked', async () => {
      wrapper = mountView()
      await flushPromises()
      await wrapper.find(getByTestId('assign-course-button')).trigger('click')
      expect(mockRouter.push).toHaveBeenCalledWith('/professor/assign-course')
    })

    it('should not show the roster table when no courses', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('student-list')).exists()).toBe(false)
    })
  })

  // ── Roster table ────────────────────────────────────────────────────────────
  describe('Roster table', () => {
    it('should display the course matiere name from the API response', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Maths')
    })

    it('should render the table even when no student has a progression for the course', async () => {
      mockCourseService.getProfessorClassCourses.mockResolvedValue(classCoursesNoProgressions)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Physique')
      expect(wrapper.find(getByTestId('student-list')).exists()).toBe(true)
    })

    it('should display the student count in the header', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('1 élève')
    })

    it('should display "élèves" (plural) when more than one student', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('2 élèves')
    })

    it('should display the student name in the table', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Alice Martin')
    })

    it('should display the student initials in the avatar', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('AM')
    })

    it('should display the progression percentage', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('80%')
    })

    it('should display "—" when the student percentage is null', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('—')
    })
  })

  // ── Badge display ──────────────────────────────────────────────────────────
  describe('Badge display', () => {
    it('should not show a badge icon when student has no badge', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('badge-icon')).exists()).toBe(false)
    })

    it('should show a badge icon when student has a badge', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('badge-icon')).exists()).toBe(true)
    })

    it('should show a badge icon for a gold badge', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('badge-icon')).exists()).toBe(true)
    })

    it('should show a badge icon for a silver badge', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailSilverBadge)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('badge-icon')).exists()).toBe(true)
    })

    it('should show a badge icon for a bronze badge', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailBronzeBadge)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('badge-icon')).exists()).toBe(true)
    })
  })
})
