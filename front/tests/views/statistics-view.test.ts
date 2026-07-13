import { vi, afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useUserStore } from '../../src/stores/userStore'

// ── Hoisted service mocks (must be before vi.mock factories) ───────────────────
const { mockCourseService, mockStatisticService } = vi.hoisted(() => ({
  mockCourseService: {
    getTopCoursesByAvg: vi.fn(),
  },
  mockStatisticService: {
    getCompletionBySubject: vi.fn(),
    getActiveStudentsPerClass: vi.fn(),
    getBadgeDistribution: vi.fn(),
    getRegistrationsOverTime: vi.fn(),
    getMyProgressions: vi.fn(),
    getMyCompetences: vi.fn(),
    getMyQuizScores: vi.fn(),
    getMyBadges: vi.fn(),
    getMyClassRank: vi.fn(),
  },
}))

// ── Chart component stubs ──────────────────────────────────────────────────────
vi.mock('@components/chart/MostCompletedCoursesChart.vue', () => ({
  default: {
    name: 'MostCompletedCoursesChart',
    template: '<div data-testid="most-completed-courses-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/CompletionBySubjectChart.vue', () => ({
  default: {
    name: 'CompletionBySubjectChart',
    template: '<div data-testid="completion-by-subject-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/ActiveStudentsPerClassChart.vue', () => ({
  default: {
    name: 'ActiveStudentsPerClassChart',
    template: '<div data-testid="active-students-per-class-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/BadgeDistributionChart.vue', () => ({
  default: {
    name: 'BadgeDistributionChart',
    template: '<div data-testid="badge-distribution-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/RegistrationsOverTimeChart.vue', () => ({
  default: {
    name: 'RegistrationsOverTimeChart',
    template: '<div data-testid="registrations-over-time-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/MyProgressionChart.vue', () => ({
  default: {
    name: 'MyProgressionChart',
    template: '<div data-testid="my-progression-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/MyCompetencesChart.vue', () => ({
  default: {
    name: 'MyCompetencesChart',
    template: '<div data-testid="my-competences-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/MyQuizScoresChart.vue', () => ({
  default: {
    name: 'MyQuizScoresChart',
    template: '<div data-testid="my-quiz-scores-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/MyBadgesChart.vue', () => ({
  default: {
    name: 'MyBadgesChart',
    template: '<div data-testid="my-badges-chart" />',
    props: { points: Array },
  },
}))

vi.mock('@components/chart/MyClassRankChart.vue', () => ({
  default: {
    name: 'MyClassRankChart',
    template: '<div data-testid="my-class-rank-chart" />',
    props: { rank: Object },
  },
}))

vi.mock('../../src/services/courseService.ts', () => ({
  courseService: mockCourseService,
}))

vi.mock('../../src/services/statisticService.ts', () => ({
  statisticService: mockStatisticService,
}))

// ── Import after mocks ─────────────────────────────────────────────────────────
import StatisticsView from '../../src/views/StatisticsView.vue'
import { topCoursesData } from '../mocks/cours'
import { completionBySubjectData } from '../mocks/matiere'
import { activeStudentsData } from '../mocks/classe'
import { badgeDistributionData, myBadgesData } from '../mocks/badge'
import { makeStudent, makeProfesseur, registrationsData, myClassRankData } from '../mocks/user'
import { myProgressionsData } from '../mocks/progression'
import { myCompetencesData } from '../mocks/competence'
import { myQuizScoresData } from '../mocks/qcm'
import type { MostCompletedCourseSinglePoint } from '../../src/types'

interface StatisticsViewInstance {
  activeTab: 'global' | 'personal'
  mostCompletedCourses: MostCompletedCourseSinglePoint[] | null
  subjectTableItems: { subject: string; average: string }[]
  topCoursesTableItems: { title: string; percent: string }[]
  activeStudentsTableItems: { classe: string; count: number }[]
  badgeTableItems: { type: string; count: number }[]
  registrationsTableItems: { week: string; count: number }[]
  myProgressionTableItems: { title: string; percentage: number }[]
  myCompetencesTableItems: { matiere: string; percentage: number }[]
  myQuizTableItems: { label: string; points: number }[]
  myBadgesTableItems: { type: string; count: number }[]
}

// ── Helpers ────────────────────────────────────────────────────────────────────
const flushPromises = () => new Promise<void>((resolve) => setTimeout(resolve, 0))

// ── Tests ──────────────────────────────────────────────────────────────────────
describe('StatisticsView', () => {
  let wrapper: VueWrapper
  let pinia: ReturnType<typeof createPinia>

  beforeEach(() => {
    // Share the same pinia between the test and the mounted component
    pinia = createPinia()
    setActivePinia(pinia)

    mockCourseService.getTopCoursesByAvg.mockResolvedValue(topCoursesData)
    mockStatisticService.getCompletionBySubject.mockResolvedValue(completionBySubjectData)
    mockStatisticService.getActiveStudentsPerClass.mockResolvedValue(activeStudentsData)
    mockStatisticService.getBadgeDistribution.mockResolvedValue(badgeDistributionData)
    mockStatisticService.getRegistrationsOverTime.mockResolvedValue(registrationsData)
    mockStatisticService.getMyProgressions.mockResolvedValue(myProgressionsData)
    mockStatisticService.getMyCompetences.mockResolvedValue(myCompetencesData)
    mockStatisticService.getMyQuizScores.mockResolvedValue(myQuizScoresData)
    mockStatisticService.getMyBadges.mockResolvedValue(myBadgesData)
    mockStatisticService.getMyClassRank.mockResolvedValue(myClassRankData)
  })

  afterEach(() => {
    wrapper?.unmount()
    vi.clearAllMocks()
  })

  const mountView = () =>
    mount(StatisticsView, {
      global: {
        plugins: [pinia],
        stubs: {
          VRow: { template: '<div><slot /></div>' },
          VCol: { template: '<div><slot /></div>' },
          VTabs: {
            name: 'VTabs',
            template: '<div><slot /></div>',
            props: ['modelValue'],
            emits: ['update:modelValue'],
          },
          VTab: { template: '<button><slot /></button>', props: ['value'] },
          VWindow: {
            name: 'VWindow',
            template: '<div><slot /></div>',
            props: ['modelValue'],
            emits: ['update:modelValue'],
          },
          VWindowItem: { template: '<div><slot /></div>', props: ['value'] },
          VDataTable: { template: '<table />', props: ['headers', 'items'] },
        },
      },
    })

  // ── Mounting ───────────────────────────────────────────────────────────────
  describe('Rendering', () => {
    it('should mount without error', () => {
      wrapper = mountView()
      expect(wrapper.exists()).toBe(true)
    })

    it('should call loadGlobal services on mount for a professor', async () => {
      useUserStore().user = makeProfesseur()
      wrapper = mountView()
      await flushPromises()

      expect(mockCourseService.getTopCoursesByAvg).toHaveBeenCalledWith(5)
      expect(mockStatisticService.getCompletionBySubject).toHaveBeenCalledOnce()
      expect(mockStatisticService.getActiveStudentsPerClass).toHaveBeenCalledOnce()
      expect(mockStatisticService.getBadgeDistribution).toHaveBeenCalledOnce()
      expect(mockStatisticService.getRegistrationsOverTime).toHaveBeenCalledOnce()
    })

    it('should not call global services on mount for a non-professor', async () => {
      useUserStore().user = makeStudent()
      wrapper = mountView()
      await flushPromises()

      expect(mockCourseService.getTopCoursesByAvg).not.toHaveBeenCalled()
      expect(mockStatisticService.getCompletionBySubject).not.toHaveBeenCalled()
      expect(mockStatisticService.getActiveStudentsPerClass).not.toHaveBeenCalled()
      expect(mockStatisticService.getBadgeDistribution).not.toHaveBeenCalled()
      expect(mockStatisticService.getRegistrationsOverTime).not.toHaveBeenCalled()
    })

    it('should not call personal services on mount for a professor', async () => {
      useUserStore().user = makeProfesseur()
      wrapper = mountView()
      await flushPromises()

      expect(mockStatisticService.getMyProgressions).not.toHaveBeenCalled()
      expect(mockStatisticService.getMyCompetences).not.toHaveBeenCalled()
      expect(mockStatisticService.getMyQuizScores).not.toHaveBeenCalled()
      expect(mockStatisticService.getMyBadges).not.toHaveBeenCalled()
      expect(mockStatisticService.getMyClassRank).not.toHaveBeenCalled()
    })

    it('should update activeTab when VTabs emits update:modelValue', async () => {
      useUserStore().user = makeStudent()
      wrapper = mountView()
      await flushPromises()

      const tabs = wrapper.findComponent({ name: 'VTabs' })
      await tabs.vm.$emit('update:modelValue', 'personal')
      await flushPromises()

      expect((wrapper.vm as unknown as StatisticsViewInstance).activeTab).toBe('personal')
    })

    it('should update activeTab when VWindow emits update:modelValue', async () => {
      useUserStore().user = makeStudent()
      wrapper = mountView()
      await flushPromises()

      const win = wrapper.findComponent({ name: 'VWindow' })
      await win.vm.$emit('update:modelValue', 'personal')
      await flushPromises()

      expect((wrapper.vm as unknown as StatisticsViewInstance).activeTab).toBe('personal')
    })
  })

  // ── Student tab visibility ─────────────────────────────────────────────────
  describe('Student tab', () => {
    it('should not show personal tab for non-student user', async () => {
      useUserStore().user = makeProfesseur()
      wrapper = mountView()
      await flushPromises()

      expect(wrapper.text()).not.toContain('Mes statistiques')
    })

    it('should show personal tab for student user', async () => {
      useUserStore().user = makeStudent()
      wrapper = mountView()
      await flushPromises()

      expect(wrapper.text()).toContain('Mes statistiques')
    })

    it('should not show personal tab when user is null', async () => {
      useUserStore().user = null
      wrapper = mountView()
      await flushPromises()

      expect(wrapper.text()).not.toContain('Mes statistiques')
    })
  })

  // ── loadPersonal lazy loading ──────────────────────────────────────────────
  describe('loadPersonal', () => {
    const mountAsStudent = async () => {
      useUserStore().user = makeStudent()
      wrapper = mountView()
      await flushPromises()
    }

    it('should load personal stats when tab switches to personal', async () => {
      await mountAsStudent()
      ;(wrapper.vm as unknown as StatisticsViewInstance).activeTab = 'personal'
      await flushPromises()

      expect(mockStatisticService.getMyProgressions).toHaveBeenCalledOnce()
      expect(mockStatisticService.getMyCompetences).toHaveBeenCalledOnce()
      expect(mockStatisticService.getMyQuizScores).toHaveBeenCalledOnce()
      expect(mockStatisticService.getMyBadges).toHaveBeenCalledOnce()
      expect(mockStatisticService.getMyClassRank).toHaveBeenCalledOnce()
    })

    it('should not reload personal stats if already loaded', async () => {
      await mountAsStudent()
      const vm = wrapper.vm as unknown as StatisticsViewInstance

      vm.activeTab = 'personal'
      await flushPromises()
      vm.activeTab = 'global'
      await flushPromises()
      vm.activeTab = 'personal'
      await flushPromises()

      expect(mockStatisticService.getMyProgressions).toHaveBeenCalledOnce()
    })

    it('should not crash when getMyClassRank rejects', async () => {
      mockStatisticService.getMyClassRank.mockRejectedValue(new Error('no rank'))
      await mountAsStudent()
      ;(wrapper.vm as unknown as StatisticsViewInstance).activeTab = 'personal'
      await expect(flushPromises()).resolves.not.toThrow()
    })
  })

  // ── Global chart props (professor only) ────────────────────────────────────
  const mountAsProfessor = async () => {
    useUserStore().user = makeProfesseur()
    wrapper = mountView()
    await flushPromises()
  }

  describe('CompletionBySubjectChart receives correct props', () => {
    it('should pass completionBySubject data as points', async () => {
      await mountAsProfessor()

      const chart = wrapper.findComponent({ name: 'CompletionBySubjectChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual(completionBySubjectData)
    })
  })

  describe('MostCompletedCoursesChart receives correct props', () => {
    it('should pass mapped top courses as points', async () => {
      await mountAsProfessor()

      const chart = wrapper.findComponent({ name: 'MostCompletedCoursesChart' })
      expect(chart.exists()).toBe(true)
      const points = chart.props('points') as MostCompletedCourseSinglePoint[]
      expect(points).toHaveLength(2)
      expect(points[0].percent).toBe(85)
      expect(points[1].percent).toBe(72)
    })
  })

  describe('ActiveStudentsPerClassChart receives correct props', () => {
    it('should pass activeStudentsPerClass data as points', async () => {
      await mountAsProfessor()

      const chart = wrapper.findComponent({ name: 'ActiveStudentsPerClassChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual(activeStudentsData)
    })
  })

  describe('BadgeDistributionChart receives correct props', () => {
    it('should pass badgeDistribution data as points', async () => {
      await mountAsProfessor()

      const chart = wrapper.findComponent({ name: 'BadgeDistributionChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual(badgeDistributionData)
    })
  })

  describe('RegistrationsOverTimeChart receives correct props', () => {
    it('should pass registrationsOverTime data as points', async () => {
      await mountAsProfessor()

      const chart = wrapper.findComponent({ name: 'RegistrationsOverTimeChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual(registrationsData)
    })
  })

  // ── Personal chart props ───────────────────────────────────────────────────
  describe('Personal chart props (student only)', () => {
    const mountAndLoadPersonal = async () => {
      useUserStore().user = makeStudent()
      wrapper = mountView()
      await flushPromises()
      ;(wrapper.vm as unknown as StatisticsViewInstance).activeTab = 'personal'
      await flushPromises()
    }

    it('MyProgressionChart should receive correct points', async () => {
      await mountAndLoadPersonal()
      const chart = wrapper.findComponent({ name: 'MyProgressionChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual(myProgressionsData)
    })

    it('MyCompetencesChart should receive correct points', async () => {
      await mountAndLoadPersonal()
      const chart = wrapper.findComponent({ name: 'MyCompetencesChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual(myCompetencesData)
    })

    it('MyQuizScoresChart should receive correct points', async () => {
      await mountAndLoadPersonal()
      const chart = wrapper.findComponent({ name: 'MyQuizScoresChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual(myQuizScoresData)
    })

    it('MyBadgesChart should receive correct points', async () => {
      await mountAndLoadPersonal()
      const chart = wrapper.findComponent({ name: 'MyBadgesChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual(myBadgesData)
    })

    it('MyClassRankChart should receive correct rank prop', async () => {
      await mountAndLoadPersonal()
      const chart = wrapper.findComponent({ name: 'MyClassRankChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('rank')).toEqual(myClassRankData)
    })
  })

  // ── v-if guards: charts hidden when data is empty ──────────────────────────
  describe('Charts are hidden when data is empty', () => {
    beforeEach(() => {
      mockCourseService.getTopCoursesByAvg.mockResolvedValue([])
      mockStatisticService.getCompletionBySubject.mockResolvedValue([])
      mockStatisticService.getActiveStudentsPerClass.mockResolvedValue([])
      mockStatisticService.getBadgeDistribution.mockResolvedValue([])
      mockStatisticService.getRegistrationsOverTime.mockResolvedValue([])
    })

    it('should hide CompletionBySubjectChart when data is empty', async () => {
      useUserStore().user = makeProfesseur()
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.findComponent({ name: 'CompletionBySubjectChart' }).exists()).toBe(false)
    })

    it('should hide ActiveStudentsPerClassChart when data is empty', async () => {
      useUserStore().user = makeProfesseur()
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.findComponent({ name: 'ActiveStudentsPerClassChart' }).exists()).toBe(false)
    })

    it('should hide BadgeDistributionChart when data is empty', async () => {
      useUserStore().user = makeProfesseur()
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.findComponent({ name: 'BadgeDistributionChart' }).exists()).toBe(false)
    })

    it('should hide RegistrationsOverTimeChart when data is empty', async () => {
      useUserStore().user = makeProfesseur()
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.findComponent({ name: 'RegistrationsOverTimeChart' }).exists()).toBe(false)
    })

    it('should show MostCompletedCoursesChart even when courses array is empty (v-if truthy check)', async () => {
      // The template uses v-if="mostCompletedCourses" (null check, not length check)
      // An empty array is truthy, so the chart renders but receives empty points
      useUserStore().user = makeProfesseur()
      wrapper = mountView()
      await flushPromises()
      const chart = wrapper.findComponent({ name: 'MostCompletedCoursesChart' })
      expect(chart.exists()).toBe(true)
      expect(chart.props('points')).toEqual([])
    })
  })

  // ── MyClassRankChart hidden when rank is null ──────────────────────────────
  describe('MyClassRankChart visibility', () => {
    it('should not render MyClassRankChart when myClassRank is null (rejected promise)', async () => {
      mockStatisticService.getMyClassRank.mockRejectedValue(new Error('no rank'))
      useUserStore().user = makeStudent()
      wrapper = mountView()
      await flushPromises()
      ;(wrapper.vm as unknown as StatisticsViewInstance).activeTab = 'personal'
      await flushPromises()

      expect(wrapper.findComponent({ name: 'MyClassRankChart' }).exists()).toBe(false)
    })
  })

  // ── Computed table items ───────────────────────────────────────────────────
  describe('Computed table items', () => {
    it('subjectTableItems should format average with toFixed(1)', async () => {
      await mountAsProfessor()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.subjectTableItems).toEqual([
        { subject: 'Maths', average: '80.0' },
        { subject: 'Physique', average: '65.0' },
      ])
    })

    it('topCoursesTableItems should map course title and format percent', async () => {
      await mountAsProfessor()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.topCoursesTableItems).toEqual([
        { title: 'Algèbre', percent: '85.0' },
        { title: 'Géométrie', percent: '72.0' },
      ])
    })

    it('topCoursesTableItems should return empty array when mostCompletedCourses is empty', async () => {
      mockCourseService.getTopCoursesByAvg.mockResolvedValue([])
      await mountAsProfessor()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.topCoursesTableItems).toEqual([])
    })

    it('topCoursesTableItems should use ?? [] fallback and hide chart when mostCompletedCourses is null', async () => {
      await mountAsProfessor()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      // Setting via the component proxy assigns to the underlying ref's .value
      vm.mostCompletedCourses = null
      await wrapper.vm.$nextTick()
      expect(vm.topCoursesTableItems).toEqual([])
      expect(wrapper.findComponent({ name: 'MostCompletedCoursesChart' }).exists()).toBe(false)
    })

    it('activeStudentsTableItems should map classe and count', async () => {
      await mountAsProfessor()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.activeStudentsTableItems).toEqual([
        { classe: '3A', count: 20 },
        { classe: '3B', count: 18 },
      ])
    })

    it('badgeTableItems should map type and count', async () => {
      await mountAsProfessor()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.badgeTableItems).toEqual([
        { type: 'bronze', count: 10 },
        { type: 'argent', count: 5 },
      ])
    })

    it('registrationsTableItems should map week and count', async () => {
      await mountAsProfessor()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.registrationsTableItems).toEqual([
        { week: '2024-W01', count: 3 },
        { week: '2024-W02', count: 7 },
      ])
    })
  })

  // ── Personal computed table items ──────────────────────────────────────────
  describe('Personal computed table items', () => {
    const mountAndLoadPersonal = async () => {
      useUserStore().user = makeStudent()
      wrapper = mountView()
      await flushPromises()
      ;(wrapper.vm as unknown as StatisticsViewInstance).activeTab = 'personal'
      await flushPromises()
    }

    it('myProgressionTableItems should map title and percentage', async () => {
      await mountAndLoadPersonal()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.myProgressionTableItems).toEqual([
        { title: 'Cours A', percentage: 50 },
        { title: 'Cours B', percentage: 100 },
      ])
    })

    it('myCompetencesTableItems should map matiere and percentage', async () => {
      await mountAndLoadPersonal()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.myCompetencesTableItems).toEqual([
        { matiere: 'Maths', percentage: 70 },
        { matiere: 'Info', percentage: 90 },
      ])
    })

    it('myQuizTableItems should map label and points', async () => {
      await mountAndLoadPersonal()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.myQuizTableItems).toEqual([
        { label: 'Quiz 1', points: 15 },
        { label: 'Quiz 2', points: 18 },
      ])
    })

    it('myBadgesTableItems should map type and count', async () => {
      await mountAndLoadPersonal()
      const vm = wrapper.vm as unknown as StatisticsViewInstance
      expect(vm.myBadgesTableItems).toEqual([
        { type: 'bronze', count: 2 },
        { type: 'or', count: 1 },
      ])
    })
  })
})
