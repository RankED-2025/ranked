import { vi, afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'
import { VAlert } from 'vuetify/components'
import { vuetifyInstance, getByTestId } from '../util/vuetify-utils'
import type { CourseContent, CourseActivity, ProfessorCourseContent } from '../../src/types'
import { defaultStatusMessageCases } from '../util/status-messages'

// ── Hoisted mocks ─────────────────────────────────────────────────────────────
const { mockCourseStore, mockRoute, mockRouter, mockCourseService, mockUser } = vi.hoisted(() => ({
  mockCourseStore: {
    getCourseContent: vi.fn(),
    updateActiviteProgression: vi.fn(),
  },
  mockRoute: { params: { id: '1' } },
  mockRouter: { push: vi.fn() },
  mockCourseService: {
    getProfessorCourseContent: vi.fn(),
    deleteCourse: vi.fn(),
  },
  mockUser: { value: { roles: ['ROLE_ELEVE'] } as { roles: string[] } | null },
}))

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...(actual as object),
    useRoute: () => mockRoute,
    useRouter: () => mockRouter,
  }
})

vi.mock('@/stores/courseStore', () => ({
  useCourseStore: () => mockCourseStore,
}))

vi.mock('@/services/courseService', () => ({
  courseService: mockCourseService,
}))

vi.mock('@/composables', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...(actual as object),
    useAuth: () => ({ user: { value: mockUser.value } }),
  }
})

// ── Import after mocks ────────────────────────────────────────────────────────
import CourseContentView from '../../src/views/Courses/CourseContentView.vue'

// ── Stubs ─────────────────────────────────────────────────────────────────────
const stubs = {
  LoadingModal: { template: '<div data-testid="loading-modal" />', props: ['message', 'size'] },
  CourseContentHeader: {
    name: 'CourseContentHeader',
    template: '<div data-testid="course-header" />',
    props: ['course', 'isProfessor'],
    emits: ['edit', 'delete'],
  },
  CourseActivitiesSidebar: {
    name: 'CourseActivitiesSidebar',
    template: '<div data-testid="activities-sidebar" />',
    props: ['activities', 'selectedActivityId', 'completedActivityIds', 'loadingActivityId', 'progression', 'isProfessor'],
    emits: ['select-activity'],
  },
  CourseActivityDetails: {
    name: 'CourseActivityDetails',
    template: '<div data-testid="activity-details" />',
    props: ['activity', 'isCompleted', 'isLoading', 'isProfessor', 'professorQcm'],
    emits: ['toggle-completed', 'quiz-completed'],
  },
  ConfirmationModal: {
    name: 'ConfirmationModal',
    template: '<div v-if="modelValue" data-testid="confirmation-modal" />',
    props: ['modelValue', 'title', 'message', 'confirmText', 'cancelText', 'isLoading'],
    emits: ['update:modelValue', 'confirm', 'cancel'],
  },
  VSnackbar: {
    template: '<div v-if="modelValue"><slot /></div>',
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

const qcmActivity: CourseActivity = {
  id: 3,
  type: 'qcm',
  ordre: 3,
  completed: false,
  qcm: { id: 30, gainPts: 10 },
}

const professorContent: ProfessorCourseContent = {
  id: 1,
  title: 'Mathématiques',
  description: 'Cours de maths',
  matiere: { id: 2, libelle: 'Maths' },
  difficulte: null,
  activites: [
    { ...activity1 },
    { ...activity2 },
    {
      ...qcmActivity,
      qcm: {
        id: 30,
        gainPts: 10,
        questions: [
          {
            id: 100,
            enonce: 'Capitale de la France ?',
            reponses: [
              { id: 1000, texte: 'Paris', isCorrect: true },
              { id: 1001, texte: 'Lyon', isCorrect: false },
            ],
          },
        ],
      },
    },
  ],
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const mountView = (): VueWrapper =>
  mount(CourseContentView, { global: { plugins: [vuetifyInstance], stubs } })

const sidebar = (wrapper: VueWrapper) =>
  wrapper.findComponent({ name: 'CourseActivitiesSidebar' })

const details = (wrapper: VueWrapper) =>
  wrapper.findComponent({ name: 'CourseActivityDetails' })

const header = (wrapper: VueWrapper) =>
  wrapper.findComponent({ name: 'CourseContentHeader' })

// ── Tests ─────────────────────────────────────────────────────────────────────
describe('CourseContentView', () => {
  let wrapper: VueWrapper

  beforeEach(() => {
    mockRoute.params.id = '1'
    mockUser.value = { roles: ['ROLE_ELEVE'] }
    mockCourseStore.getCourseContent.mockResolvedValue(mockContent)
    mockCourseStore.updateActiviteProgression.mockResolvedValue(true)
    mockCourseService.getProfessorCourseContent.mockResolvedValue(professorContent)
    mockCourseService.deleteCourse.mockResolvedValue({ message: 'ok' })
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

    // CourseContentView passes no overrides to StatusAlert, so every status must show
    // the shared DEFAULT_STATUS_MESSAGES message — generated from it directly.
    describe.each(
      defaultStatusMessageCases()
    )('when getCourseContent rejects with status $status', ({ status, message, type }) => {
      it(`shows the default "${type}" message`, async () => {
        mockCourseStore.getCourseContent.mockRejectedValue({ response: { status } })
        wrapper = mountView()
        await flushPromises()

        const alert = wrapper.get(getByTestId('error-message'))
        expect(alert.text()).toBe(message)
        expect(wrapper.findComponent(VAlert).props('type')).toBe(type)
      })
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

  // ── Professor mode ────────────────────────────────────────────────────────
  describe('Professor mode', () => {
    it('does not fetch the professor content when viewed by a student', async () => {
      wrapper = mountView()
      await flushPromises()
      expect(mockCourseService.getProfessorCourseContent).not.toHaveBeenCalled()
    })

    it('fetches the professor content and passes isProfessor down when viewed by a professor', async () => {
      mockUser.value = { roles: ['ROLE_PROFESSEUR'] }
      wrapper = mountView()
      await flushPromises()

      expect(mockCourseService.getProfessorCourseContent).toHaveBeenCalledWith('1')
      expect(header(wrapper).props('isProfessor')).toBe(true)
      expect(sidebar(wrapper).props('isProfessor')).toBe(true)
      expect(details(wrapper).props('isProfessor')).toBe(true)
    })

    it('passes the matching professor qcm (with questions) to the details panel', async () => {
      mockUser.value = { roles: ['ROLE_PROFESSEUR'] }
      mockCourseStore.getCourseContent.mockResolvedValue({
        ...mockContent,
        activites: [activity1, activity2, qcmActivity],
      })
      wrapper = mountView()
      await flushPromises()

      sidebar(wrapper).vm.$emit('select-activity', 3)
      await nextTick()

      const passedQcm = details(wrapper).props('professorQcm')
      expect(passedQcm.gainPts).toBe(10)
      expect(passedQcm.questions).toHaveLength(1)
      expect(passedQcm.questions[0].reponses[0]).toMatchObject({ texte: 'Paris', isCorrect: true })
    })

    it('hides the progression bar and shows the preview banner instead', async () => {
      mockUser.value = { roles: ['ROLE_PROFESSEUR'] }
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find('.progression-bar-wrapper').exists()).toBe(false)
      expect(wrapper.find('.preview-banner').exists()).toBe(true)
    })

    it('never shows "Cours terminé !" even at 100% completion', async () => {
      mockUser.value = { roles: ['ROLE_PROFESSEUR'] }
      mockCourseStore.getCourseContent.mockResolvedValue(allCompletedContent)
      wrapper = mountView()
      await flushPromises()
      expect(wrapper.find(getByTestId('fully-completed')).exists()).toBe(false)
    })

    it('navigates to the edit page when the header emits edit', async () => {
      mockUser.value = { roles: ['ROLE_PROFESSEUR'] }
      wrapper = mountView()
      await flushPromises()

      header(wrapper).vm.$emit('edit')
      await nextTick()

      expect(mockRouter.push).toHaveBeenCalledWith('/professor/edit-course/1')
    })

    it('opens the confirmation modal when the header emits delete', async () => {
      mockUser.value = { roles: ['ROLE_PROFESSEUR'] }
      wrapper = mountView()
      await flushPromises()

      header(wrapper).vm.$emit('delete')
      await nextTick()

      expect(wrapper.find(getByTestId('confirmation-modal')).exists()).toBe(true)
    })

    it('deletes the course and redirects on confirm', async () => {
      mockUser.value = { roles: ['ROLE_PROFESSEUR'] }
      wrapper = mountView()
      await flushPromises()

      header(wrapper).vm.$emit('delete')
      await nextTick()

      wrapper.findComponent({ name: 'ConfirmationModal' }).vm.$emit('confirm')
      await flushPromises()

      expect(mockCourseService.deleteCourse).toHaveBeenCalledWith('1')
      expect(mockRouter.push).toHaveBeenCalledWith('/professor/my-courses')
    })

    it('shows an error snackbar when deletion fails', async () => {
      mockUser.value = { roles: ['ROLE_PROFESSEUR'] }
      mockCourseService.deleteCourse.mockRejectedValue(new Error('boom'))
      wrapper = mountView()
      await flushPromises()

      header(wrapper).vm.$emit('delete')
      await nextTick()

      wrapper.findComponent({ name: 'ConfirmationModal' }).vm.$emit('confirm')
      await flushPromises()

      expect(wrapper.find(getByTestId('delete-error-message')).exists()).toBe(true)
      expect(mockRouter.push).not.toHaveBeenCalledWith('/professor/my-courses')
    })
  })
})
