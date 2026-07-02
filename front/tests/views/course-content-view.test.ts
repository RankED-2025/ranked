import { vi, afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'
import { vuetifyInstance, getByTestId } from '../util/vuetify-utils'
import type { CourseContent, CourseActivity } from '../../src/types'

// ── Hoisted mocks ─────────────────────────────────────────────────────────────
const { mockCourseStore, mockRoute } = vi.hoisted(() => ({
  mockCourseStore: {
    getCourseContent: vi.fn(),
    updateActiviteProgression: vi.fn(),
  },
  mockRoute: { params: { id: '1' } },
}))

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...(actual as object),
    useRoute: () => mockRoute,
  }
})

vi.mock('@/stores/courseStore', () => ({
  useCourseStore: () => mockCourseStore,
}))

// ── Import after mocks ────────────────────────────────────────────────────────
import CourseContentView from '../../src/views/Courses/CourseContentView.vue'

// ── Stubs ─────────────────────────────────────────────────────────────────────
const stubs = {
  LoadingModal: { template: '<div data-testid="loading-modal" />', props: ['message', 'size'] },
  CourseContentHeader: { template: '<div data-testid="course-header" />', props: ['course'] },
  CourseActivitiesSidebar: {
    name: 'CourseActivitiesSidebar',
    template: '<div data-testid="activities-sidebar" />',
    props: ['activities', 'selectedActivityId', 'completedActivityIds', 'loadingActivityId', 'progression'],
    emits: ['select-activity'],
  },
  CourseActivityDetails: {
    name: 'CourseActivityDetails',
    template: '<div data-testid="activity-details" />',
    props: ['activity', 'isCompleted', 'isLoading'],
    emits: ['toggle-completed'],
  },
  VSnackbar: {
    template: '<div v-if="modelValue" data-testid="toggle-error"><slot /></div>',
    props: ['modelValue', 'color', 'timeout', 'location'],
  },
}

// ── Mock data ─────────────────────────────────────────────────────────────────
const makeActivity = (id: number, ordre: number, completed: boolean): CourseActivity => ({
  id,
  type: 'contenu',
  ordre,
  completed,
})

const activity1 = makeActivity(1, 2, false)
const activity2 = makeActivity(2, 1, true)

const mockContent: CourseContent = {
  id: 1,
  title: 'Mathématiques',
  description: 'Cours de maths',
  professeur: { id: 10, nom: 'Martin', prenom: 'Alice' },
  matiere: { id: 2, libelle: 'Maths' },
  difficulte: null,
  activites: [activity1, activity2],
}

const allCompletedContent: CourseContent = {
  ...mockContent,
  activites: [makeActivity(1, 1, true), makeActivity(2, 2, true)],
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const mountView = (): VueWrapper =>
  mount(CourseContentView, { global: { plugins: [vuetifyInstance], stubs } })

const sidebar = (wrapper: VueWrapper) =>
  wrapper.findComponent({ name: 'CourseActivitiesSidebar' })

const details = (wrapper: VueWrapper) =>
  wrapper.findComponent({ name: 'CourseActivityDetails' })

// ── Tests ─────────────────────────────────────────────────────────────────────
describe('CourseContentView', () => {
  let wrapper: VueWrapper

  beforeEach(() => {
    mockRoute.params.id = '1'
    mockCourseStore.getCourseContent.mockResolvedValue(mockContent)
    mockCourseStore.updateActiviteProgression.mockResolvedValue(true)
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

    it('should show the loading state while fetching', async () => {
      mockCourseStore.getCourseContent.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await nextTick()
      expect(wrapper.find(getByTestId('loading')).exists()).toBe(true)
      expect(wrapper.find(getByTestId('error-message')).exists()).toBe(false)
      expect(wrapper.find(getByTestId('activities-sidebar')).exists()).toBe(false)
    })

    it('should hide loading and show content after fetching', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('loading')).exists()).toBe(false)
      expect(wrapper.find(getByTestId('activities-sidebar')).exists()).toBe(true)
    })

    it('should show an error when the course id is missing', async () => {
      mockRoute.params.id = ''
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('error-message')).exists()).toBe(true)
      expect(wrapper.text()).toContain('Identifiant de cours invalide.')
      expect(mockCourseStore.getCourseContent).not.toHaveBeenCalled()
    })

    it('should show an error when getCourseContent throws', async () => {
      mockCourseStore.getCourseContent.mockRejectedValue({ response: { status: 404 } })
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('error-message')).exists()).toBe(true)
      expect(wrapper.find(getByTestId('activities-sidebar')).exists()).toBe(false)
    })

    it('should call getCourseContent with the route id', async () => {
      mockRoute.params.id = '42'
      wrapper = mountView()
      await flushPromises()
      expect(mockCourseStore.getCourseContent).toHaveBeenCalledWith('42')
    })

    it('should show "Cours terminé !" when all activities are completed', async () => {
      mockCourseStore.getCourseContent.mockResolvedValue(allCompletedContent)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('fully-completed')).exists()).toBe(true)
      expect(wrapper.find(getByTestId('fully-completed')).text()).toContain('Cours terminé !')
    })

    it('should not show "Cours terminé !" when not all activities are completed', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('fully-completed')).exists()).toBe(false)
    })
  })

  // ── Activity management ───────────────────────────────────────────────────
  describe('Activity management', () => {
    it('should pass activities sorted by ordre to the sidebar', async () => {
      wrapper = mountView()
      await flushPromises()
      const passed = sidebar(wrapper).props('activities') as CourseActivity[]
      expect(passed[0].id).toBe(2)
      expect(passed[1].id).toBe(1)
    })

    it('should initialize completedActivityIds from completed activities', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(sidebar(wrapper).props('completedActivityIds')).toEqual([2])
    })

    it('should set the first sorted activity as selected by default', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(details(wrapper).props('activity')).toMatchObject({ id: 2 })
    })

    it('should update the selected activity when select-activity is emitted', async () => {
      wrapper = mountView()
      await flushPromises()
      sidebar(wrapper).vm.$emit('select-activity', 1)
      await nextTick()
      expect(details(wrapper).props('activity')).toMatchObject({ id: 1 })
    })

    it('should pass null as selectedActivityId when no course content is available', async () => {
      mockCourseStore.getCourseContent.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await nextTick()
      expect(wrapper.find(getByTestId('activities-sidebar')).exists()).toBe(false)
    })
  })

  // ── Progression computed ──────────────────────────────────────────────────
  describe('Progression', () => {
    it('should pass 0 as progression when no activities', async () => {
      mockCourseStore.getCourseContent.mockResolvedValue({ ...mockContent, activites: [] })
      wrapper = mountView()
      await flushPromises()
      expect(sidebar(wrapper).props('progression')).toBe(0)
    })

    it('should pass the correct progression percentage to the sidebar', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(sidebar(wrapper).props('progression')).toBe(50)
    })

    it('should pass 100 as progression when all activities are completed', async () => {
      mockCourseStore.getCourseContent.mockResolvedValue(allCompletedContent)
      wrapper = mountView()
      await flushPromises()
      expect(sidebar(wrapper).props('progression')).toBe(100)
    })
  })

  // ── toggleCompleted ───────────────────────────────────────────────────────
  describe('toggleCompleted', () => {
    it('should optimistically mark an activity as completed before the API responds', async () => {
      mockCourseStore.updateActiviteProgression.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 1)
      await nextTick()

      expect(sidebar(wrapper).props('completedActivityIds')).toContain(1)
    })

    it('should optimistically unmark an activity before the API responds', async () => {
      mockCourseStore.updateActiviteProgression.mockReturnValue(new Promise(() => {}))
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 2)
      await nextTick()

      expect(sidebar(wrapper).props('completedActivityIds')).not.toContain(2)
    })

    it('should keep the updated state when the API call succeeds', async () => {
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 1)
      await flushPromises()

      expect(sidebar(wrapper).props('completedActivityIds')).toContain(1)
    })

    it('should revert the optimistic update when the API call fails', async () => {
      mockCourseStore.updateActiviteProgression.mockResolvedValue(false)
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 1)
      await flushPromises()

      expect(sidebar(wrapper).props('completedActivityIds')).not.toContain(1)
    })

    it('should revert an unmark when the API call fails', async () => {
      mockCourseStore.updateActiviteProgression.mockResolvedValue(false)
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 2)
      await flushPromises()

      expect(sidebar(wrapper).props('completedActivityIds')).toContain(2)
    })

    it('should show the error snackbar when the API call fails', async () => {
      mockCourseStore.updateActiviteProgression.mockResolvedValue(false)
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 1)
      await flushPromises()

      expect(wrapper.find(getByTestId('toggle-error')).exists()).toBe(true)
    })

    it('should not show the error snackbar on success', async () => {
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 1)
      await flushPromises()

      expect(wrapper.find(getByTestId('toggle-error')).exists()).toBe(false)
    })

    it('should set loadingActivityId on the sidebar during the API call', async () => {
      let resolve: (v: boolean) => void
      mockCourseStore.updateActiviteProgression.mockReturnValue(
        new Promise((r) => { resolve = r }),
      )
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 1)
      await nextTick()

      expect(sidebar(wrapper).props('loadingActivityId')).toBe(1)

      resolve!(true)
      await flushPromises()

      expect(sidebar(wrapper).props('loadingActivityId')).toBeNull()
    })

    it('should pass isLoading true to CourseActivityDetails for the loading activity', async () => {
      let resolve: (v: boolean) => void
      mockCourseStore.updateActiviteProgression.mockReturnValue(
        new Promise((r) => { resolve = r }),
      )
      wrapper = mountView()
      await flushPromises()

      sidebar(wrapper).vm.$emit('select-activity', 1)
      await nextTick()

      details(wrapper).vm.$emit('toggle-completed', 1)
      await nextTick()

      expect(details(wrapper).props('isLoading')).toBe(true)

      resolve!(true)
      await flushPromises()

      expect(details(wrapper).props('isLoading')).toBe(false)
    })

    it('should call updateActiviteProgression with the correct arguments', async () => {
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 1)
      await flushPromises()

      expect(mockCourseStore.updateActiviteProgression).toHaveBeenCalledWith(1, true)
    })

    it('should call updateActiviteProgression with false when unmarking', async () => {
      wrapper = mountView()
      await flushPromises()

      details(wrapper).vm.$emit('toggle-completed', 2)
      await flushPromises()

      expect(mockCourseStore.updateActiviteProgression).toHaveBeenCalledWith(2, false)
    })
  })
})
