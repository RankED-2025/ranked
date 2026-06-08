import { vi, afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'
import { globalTestPlugins } from '../../util/vuetify-utils'
import { bestStudentsData } from '../../mocks/best-students'

// ── Test-specific types ────────────────────────────────────────────────────
type LoaderWrapperVm = {
  loading: boolean
}

// Holds the target components methods' signatures.
type ComponentCallable = {
  rankColor: (rank: number) => string
  progressColor: (pct: number) => string
}

// ── Hoisted service mock ────────────────────────────────────────────────────
const { mockStatisticService } = vi.hoisted(() => ({
  mockStatisticService: {
    getBestStudents: vi.fn(),
  },
}))

vi.mock('../../../src/services/statisticService.ts', () => ({
  statisticService: mockStatisticService,
}))

// ── Import after mocks ──────────────────────────────────────────────────────
import BestStudentsCard from '../../../src/components/professor/BestStudentsCard.vue'

// ── Helpers ─────────────────────────────────────────────────────────────────
const mountComponent = (classeId = 1, limit?: number): VueWrapper =>
  mount(BestStudentsCard, {
    props: limit !== undefined ? { classeId, limit } : { classeId },
    global: { plugins: globalTestPlugins },
  })

// ── Tests ────────────────────────────────────────────────────────────────────
describe('BestStudentsCard', () => {
  let wrapper: VueWrapper

  beforeEach(() => {
    mockStatisticService.getBestStudents.mockResolvedValue(bestStudentsData)
  })

  afterEach(() => {
    wrapper?.unmount()
    vi.clearAllMocks()
  })

  // ── Rendering ─────────────────────────────────────────────────────────────
  describe('Rendering', () => {
    it('should mount without error', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.exists()).toBe(true)
    })

    it('should display the "Meilleurs élèves" title', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).toContain('Meilleurs élèves')
    })

    it('should display the Top chip with the default limit', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).toContain('Top 5')
    })

    it.each([
      { limit: 1 },
      { limit: 10 },
      { limit: 2500 },
      { limit: 999 },
      { limit: Number.MAX_VALUE },
    ])(
      'should display the Top chip with a custom limit (limit=$limit)', async ({ limit }) => {
        wrapper = mountComponent(1, limit)
        await flushPromises()
        expect(wrapper.text()).toContain('Top ' + limit)
      },
    )
  })

  // ── Service call ──────────────────────────────────────────────────────────
  describe('Service call', () => {
    it('should call getBestStudents with classeId and default limit 5', async () => {
      wrapper = mountComponent(42)
      await flushPromises()
      expect(mockStatisticService.getBestStudents).toHaveBeenCalledWith(42, 5)
    })

    it('should call getBestStudents with the provided limit', async () => {
      wrapper = mountComponent(7, 3)
      await flushPromises()
      expect(mockStatisticService.getBestStudents).toHaveBeenCalledWith(7, 3)
    })

    it('should call getBestStudents exactly once on mount', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect(mockStatisticService.getBestStudents).toHaveBeenCalledOnce()
    })
  })

  // ── Loading state ─────────────────────────────────────────────────────────
  describe('Loading state', () => {
    it('should be in loading state before data resolves', async () => {
      mockStatisticService.getBestStudents.mockReturnValue(new Promise(() => {}))
      wrapper = mountComponent()
      await nextTick()
      expect((wrapper.vm as unknown as LoaderWrapperVm).loading).toBe(true)
    })

    it('should not show the student list while loading', async () => {
      mockStatisticService.getBestStudents.mockReturnValue(new Promise(() => {}))
      wrapper = mountComponent()
      await nextTick()
      expect(wrapper.find('.v-list').exists()).toBe(false)
    })

    it('should set loading to false after data resolves', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect((wrapper.vm as unknown as LoaderWrapperVm).loading).toBe(false)
    })
  })

  // ── Data state ────────────────────────────────────────────────────────────
  describe('Data state', () => {
    it('should display student names after data loads', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).toContain('Alice Martin')
      expect(wrapper.text()).toContain('Bob Dupont')
      expect(wrapper.text()).toContain('Clara Leroy')
    })

    it('should display course completion counts for each student', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).toContain('8/10 cours terminés')
    })

    it('should display average percentage for each student', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).toContain('92%')
      expect(wrapper.text()).toContain('65%')
    })

    it('should show topSubject chip when topSubject is set', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).toContain('Maths')
      expect(wrapper.text()).toContain('Physique')
    })

    it('should not show topSubject chip when topSubject is null', async () => {
      mockStatisticService.getBestStudents.mockResolvedValue([
        { rank: 1, name: 'A', firstname: 'B', average: 80, completedCourses: 5, totalCourses: 10, topSubject: null },
      ])
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).not.toContain('null')
      expect(wrapper.find('[data-testid="top-subject-chip"]').exists()).toBe(false)
    })
  })

  // ── Empty state ───────────────────────────────────────────────────────────
  describe('Empty state', () => {
    it('should display the empty message when no students are returned', async () => {
      mockStatisticService.getBestStudents.mockResolvedValue([])
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).toContain('Aucune donnée disponible pour cette classe.')
    })

    it('should not render the student list when students is empty', async () => {
      mockStatisticService.getBestStudents.mockResolvedValue([])
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.find('.v-list').exists()).toBe(false)
    })
  })

  // ── Error state ───────────────────────────────────────────────────────────
  describe('Error state', () => {
    it('should display the error message when the service rejects', async () => {
      mockStatisticService.getBestStudents.mockRejectedValue(new Error('network error'))
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.text()).toContain('Impossible de charger le classement des élèves.')
    })

    it('should not render the student list on error', async () => {
      mockStatisticService.getBestStudents.mockRejectedValue(new Error('network error'))
      wrapper = mountComponent()
      await flushPromises()
      expect(wrapper.find('.v-list').exists()).toBe(false)
    })
  })

  // ── rankColor ─────────────────────────────────────────────────────────────
  describe('rankColor', () => {
    it.each([
      { rank: 1, color: 'amber-darken-2' },
      { rank: 2, color: 'grey' },
      { rank: 3, color: 'deep-orange' },
    ])('should return "$color" for rank $rank', async ({ color, rank }) => {
      wrapper = mountComponent()
      await flushPromises()
      expect((wrapper.vm as unknown as ComponentCallable).rankColor(rank)).toBe(color)
    })

    it('should return "primary" for rank > 3', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect((wrapper.vm as unknown as ComponentCallable).rankColor(4)).toBe('primary')
      expect((wrapper.vm as unknown as ComponentCallable).rankColor(10)).toBe('primary')
    })
  })

  // ── progressColor ─────────────────────────────────────────────────────────
  describe('progressColor', () => {
    it('should return "success" for average >= 80', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect((wrapper.vm as unknown as ComponentCallable).progressColor(80)).toBe('success')
      expect((wrapper.vm as unknown as ComponentCallable).progressColor(100)).toBe('success')
    })

    it('should return "warning" for 50 <= average < 80', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect((wrapper.vm as unknown as ComponentCallable).progressColor(50)).toBe('warning')
      expect((wrapper.vm as unknown as ComponentCallable).progressColor(79)).toBe('warning')
    })

    it('should return "error" for average < 50', async () => {
      wrapper = mountComponent()
      await flushPromises()
      expect((wrapper.vm as unknown as ComponentCallable).progressColor(49)).toBe('error')
      expect((wrapper.vm as unknown as ComponentCallable).progressColor(0)).toBe('error')
    })
  })
})
