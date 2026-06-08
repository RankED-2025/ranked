import { vi, afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'
import { vuetifyInstance, getByTestId } from '../util/vuetify-utils'
import {
  classDetail,
  classDetailNoCourses,
  classDetailMultiStudents,
  classDetailNullCours,
  classDetailNullPercentage,
  classDetailSilverBadge,
  classDetailBronzeBadge,
  classDetailBothStudentsWithProgressions,
} from '../mocks/classe'
import type { StudentCourseProgression } from '../../src/types'

// ── Test-specific types ───────────────────────────────────────────────────────────
type VmOfComponent = {
  loading: boolean
  classId: number
  progressColor: (pct: number | null) => string
  assignedCourses: Array<{
    id: number
    matiere: { id: number; libelle: string }
  }>
  studentProgressionsByCourse: Record<number, StudentCourseProgression[]>
}

// ── Hoisted mocks ───────────────────────────────────────────────────────────
const { mockCourseService, mockRoute, mockRouter } = vi.hoisted(() => ({
  mockCourseService: {
    getProfessorClassDetail: vi.fn(),
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
// VListItem renders named slots so #prepend and #append content is covered
const stubs = {
  VContainer: { template: '<div><slot /></div>' },
  VProgressCircular: { template: '<div class="v-progress-circular" />' },
  VAlert: { template: '<div class="v-alert"><slot /></div>', props: ['type'] },
  VBtn: { template: '<button><slot /></button>' },
  VIcon: { template: '<span><slot /></span>' },
  VCard: { template: '<div class="v-card"><slot /></div>' },
  VCardTitle: { template: '<div><slot /></div>' },
  VCardText: { template: '<div><slot /></div>' },
  VList: { template: '<ul><slot /></ul>' },
  VListItem: { template: '<li><slot name="prepend" /><slot /><slot name="append" /></li>' },
  VListItemTitle: { template: '<div><slot /></div>' },
  VListItemSubtitle: { template: '<div><slot /></div>' },
  VAvatar: { template: '<div><slot /></div>' },
  VChip: { template: '<span><slot /></span>' },
  VSpacer: { template: '<span />' },
  VProgressLinear: { template: '<div />', props: ['modelValue', 'color'] },
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

    it('should show only the error alert when the service rejects', async () => {
      mockCourseService.getProfessorClassDetail.mockRejectedValue(new Error('api error'))
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('error-alert')).exists()).toBe(true)
      expect(wrapper.text()).toContain('Impossible de charger les données de la classe.')
      expect(wrapper.find(getByTestId('loading-spinner')).exists()).toBe(false)
      expect(wrapper.find(getByTestId('best-students-card')).exists()).toBe(false)
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

    it('should return "success" for percentage exactly 100', () => {
      expect((wrapper.vm as unknown as VmOfComponent).progressColor(100)).toBe('success')
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

  // ── assignedCourses computed ───────────────────────────────────────────────
  describe('assignedCourses computed', () => {
    it('should return [] when classDetail is null', async () => {
      mockCourseService.getProfessorClassDetail.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await nextTick()
      expect((wrapper.vm as unknown as VmOfComponent).assignedCourses).toEqual([])
    })

    it('should return the unique courses extracted from student progressions', async () => {
      wrapper = mountView()
      await flushPromises()
      const courses = (wrapper.vm as unknown as VmOfComponent).assignedCourses
      expect(courses).toHaveLength(1)
      expect(courses[0].id).toBe(10)
      expect(courses[0].matiere.libelle).toBe('Maths')
    })

    it('should deduplicate courses shared by multiple students', async () => {
      // Two students with the same course → only one entry in assignedCourses
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      expect((wrapper.vm as unknown as VmOfComponent).assignedCourses).toHaveLength(1)
    })

    it('should return [] when all progressions have cours: null', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNullCours)
      wrapper = mountView()
      await flushPromises()
      expect((wrapper.vm as unknown as VmOfComponent).assignedCourses).toEqual([])
    })
  })

  // ── studentProgressionsByCourse computed ──────────────────────────────────
  describe('studentProgressionsByCourse computed', () => {
    it('should return {} when classDetail is null', async () => {
      mockCourseService.getProfessorClassDetail.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await nextTick()
      expect((wrapper.vm as unknown as VmOfComponent).studentProgressionsByCourse).toEqual({})
    })

    it('should map each student to their course progression', async () => {
      wrapper = mountView()
      await flushPromises()
      const map = (wrapper.vm as unknown as VmOfComponent).studentProgressionsByCourse
      expect(map[10]).toHaveLength(1)
      expect(map[10][0]).toMatchObject({ id: 1, name: 'Martin', firstname: 'Alice', percentage: 80 })
    })

    it('should skip progressions where cours is null', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNullCours)
      wrapper = mountView()
      await flushPromises()
      // no courses → the by-course map has no entries
      expect((wrapper.vm as unknown as VmOfComponent).studentProgressionsByCourse).toEqual({})
    })

    it('should set percentage to null when prog.percentage is undefined', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNullPercentage)
      wrapper = mountView()
      await flushPromises()
      const map = (wrapper.vm as unknown as VmOfComponent).studentProgressionsByCourse
      expect(map[10][0].percentage).toBeNull()
    })

    it('should add students without progressions for a course with null percentage', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      const map = (wrapper.vm as unknown as VmOfComponent).studentProgressionsByCourse
      // Bob (id=2) has no progression → should be added with percentage: null
      const bob = map[10].find((s: StudentCourseProgression) => s.id === 2)
      expect(bob).toBeDefined()
      expect(bob.percentage).toBeNull()
      expect(bob.badge).toBeNull()
    })

    it('should include both students for the same course', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      const map = (wrapper.vm as unknown as VmOfComponent).studentProgressionsByCourse
      expect(map[10]).toHaveLength(2)
    })

    it('should reuse an existing course entry when two students both have a progression for it', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailBothStudentsWithProgressions)
      wrapper = mountView()
      await flushPromises()
      const map = (wrapper.vm as unknown as VmOfComponent).studentProgressionsByCourse
      expect(map[10]).toHaveLength(2)
      expect(map[10].find((s: StudentCourseProgression) => s.id === 1)?.percentage).toBe(95)
      expect(map[10].find((s: StudentCourseProgression) => s.id === 2)?.percentage).toBe(60)
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
    it('should show the "no courses" card when assignedCourses is empty', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNoCourses)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Aucun cours assigné à cette classe')
    })

    it('should show the "assign course" button when no courses', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNoCourses)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Assigner un cours')
    })

    it('should navigate to /professor/assign-course when the button is clicked', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNoCourses)
      wrapper = mountView()
      await flushPromises()
      await wrapper.find(getByTestId('assign-course-button')).trigger('click')
      expect(mockRouter.push).toHaveBeenCalledWith('/professor/assign-course')
    })

    it('should not show the student progression list when no courses', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailNoCourses)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('student-list')).exists()).toBe(false)
    })
  })

  // ── Course card with students ──────────────────────────────────────────────
  describe('Course card with students', () => {
    it('should display the course matiere name', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Maths')
    })

    it('should display the student count chip', async () => {
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

    it('should display the student name in the list', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.text()).toContain('Alice Martin')
    })

    it('should display the student initials in the avatar', async () => {
      wrapper = mountView()
      await flushPromises()
      // prepend slot renders initials: "AM"
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
      // Bob has no progression → null percentage → should show "—"
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

    it('should use amber-darken-2 color for a gold badge', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailMultiStudents)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('badge-icon')).exists()).toBe(true)
    })

    it('progressColor should use "grey" color for silver badge text color', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailSilverBadge)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('badge-icon')).exists()).toBe(true)
    })

    it('progressColor should use "deep-orange" color for bronze badge text color', async () => {
      mockCourseService.getProfessorClassDetail.mockResolvedValue(classDetailBronzeBadge)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('badge-icon')).exists()).toBe(true)
    })
  })
})
