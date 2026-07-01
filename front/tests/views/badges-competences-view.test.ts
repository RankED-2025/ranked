import { vi, afterEach, beforeEach, describe, expect, it } from 'vitest'
import { mount, VueWrapper, flushPromises } from '@vue/test-utils'
import type { MyBadgeDetail } from '../../src/types'
import type { MyCompetenceDetail } from '../../src/types'

// ── Hoisted mocks ──────────────────────────────────────────────────────────────
const { mockStatisticService, mockRouter } = vi.hoisted(() => ({
  mockStatisticService: {
    getMyBadgesDetail: vi.fn(),
    getMyCompetencesDetail: vi.fn(),
  },
  mockRouter: { push: vi.fn() },
}))

vi.mock('../../src/services/statisticService.ts', () => ({
  statisticService: mockStatisticService,
}))

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...(actual as object),
    useRouter: () => mockRouter,
  }
})

vi.mock('@components/layouts/BadgeElement.vue', () => ({
  default: {
    name: 'BadgeElement',
    template: '<div data-testid="badge-element" />',
    props: ['badgeName'],
  },
}))

vi.mock('@components/loading/LoadingElement.vue', () => ({
  default: {
    name: 'LoadingElement',
    template: '<div data-testid="loading-element" />',
  },
}))

// ── Import after mocks ─────────────────────────────────────────────────────────
import BadgesCompetencesView from '../../src/views/BadgesCompetencesView.vue'
import { myBadgesDetailData } from '../mocks/badge'
import { myCompetencesDetailData } from '../mocks/competence'

// ── Types ──────────────────────────────────────────────────────────────────────
interface ViewInstance {
  activeTab: 'badges' | 'competences'
  badges: MyBadgeDetail[]
  competences: MyCompetenceDetail[]
  openPanels: string[]
  openCompetencePanels: string[]
  acquiredBadges: MyBadgeDetail[]
  inProgressBadges: MyBadgeDetail[]
  acquiredCompetences: MyCompetenceDetail[]
  inProgressCompetences: MyCompetenceDetail[]
  competencesByMatiere: Record<string, MyCompetenceDetail[]>
  goToCourse: (id: number) => void
}

// ── Stubs ──────────────────────────────────────────────────────────────────────
const stubs = {
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
  VExpansionPanels: {
    name: 'VExpansionPanels',
    template: '<div><slot /></div>',
    props: ['modelValue', 'multiple', 'variant', 'rounded', 'elevation'],
    emits: ['update:modelValue'],
  },
  VExpansionPanel: {
    name: 'VExpansionPanel',
    template: '<div><slot /></div>',
    props: ['value', 'elevation'],
  },
  VExpansionPanelTitle: { template: '<div><slot /></div>' },
  VExpansionPanelText: { template: '<div><slot /></div>', props: ['class'] },
  VIcon: { template: '<span><slot /></span>', props: ['color', 'size', 'start'] },
  VChip: { template: '<span><slot /></span>', props: ['color', 'size', 'variant'] },
  VBtn: {
    name: 'VBtn',
    template: '<button @click="$emit(\'click\')"><slot /></button>',
    props: ['size', 'variant', 'color', 'prependIcon', 'appendIcon'],
    emits: ['click'],
  },
  VCard: { template: '<div><slot /></div>', props: ['elevation', 'rounded', 'class'] },
  VProgressLinear: { template: '<div />', props: ['modelValue', 'color', 'height', 'rounded'] },
  VList: { template: '<ul><slot /></ul>', props: ['lines', 'class'] },
  VListItem: { template: '<li><slot /><slot name="prepend" /><slot name="append" /></li>' },
  VListItemTitle: { template: '<div><slot /></div>' },
  VListItemSubtitle: { template: '<div><slot /></div>' },
  VDivider: { template: '<hr />' },
}

const mountView = () =>
  mount(BadgesCompetencesView, {
    global: { stubs },
  })

// ── Tests ──────────────────────────────────────────────────────────────────────
describe('BadgesCompetencesView', () => {
  let wrapper: VueWrapper

  beforeEach(() => {
    mockStatisticService.getMyBadgesDetail.mockResolvedValue(myBadgesDetailData)
    mockStatisticService.getMyCompetencesDetail.mockResolvedValue(myCompetencesDetailData)
  })

  afterEach(() => {
    wrapper?.unmount()
    vi.clearAllMocks()
  })

  // ── Mounting ───────────────────────────────────────────────────────────────
  describe('Rendering', () => {
    it('should mount without error', () => {
      wrapper = mountView()
      expect(wrapper.exists()).toBe(true)
    })

    it('should call both service methods on mount', async () => {
      wrapper = mountView()
      await flushPromises()

      expect(mockStatisticService.getMyBadgesDetail).toHaveBeenCalledOnce()
      expect(mockStatisticService.getMyCompetencesDetail).toHaveBeenCalledOnce()
    })

    it('should default to badges tab', () => {
      wrapper = mountView()
      expect((wrapper.vm as unknown as ViewInstance).activeTab).toBe('badges')
    })
  })

  // ── Computed: badges ───────────────────────────────────────────────────────
  describe('acquiredBadges', () => {
    it('should only include badges at 100%', async () => {
      wrapper = mountView()
      await flushPromises()
      const vm = wrapper.vm as unknown as ViewInstance

      expect(vm.acquiredBadges).toHaveLength(1)
      expect(vm.acquiredBadges[0].courseId).toBe(1)
      expect(vm.acquiredBadges[0].percentage).toBe(100)
    })

    it('should return empty when all badges are in-progress', async () => {
      mockStatisticService.getMyBadgesDetail.mockResolvedValue([
        { courseId: 1, courseTitle: 'A', badgeType: 'bronze', badgeLabel: 'Bronze', percentage: 50 },
      ])
      wrapper = mountView()
      await flushPromises()

      expect((wrapper.vm as unknown as ViewInstance).acquiredBadges).toHaveLength(0)
    })
  })

  describe('inProgressBadges', () => {
    it('should only include badges below 100%', async () => {
      wrapper = mountView()
      await flushPromises()
      const vm = wrapper.vm as unknown as ViewInstance

      expect(vm.inProgressBadges).toHaveLength(2)
      expect(vm.inProgressBadges.every((b) => b.percentage < 100)).toBe(true)
    })

    it('should return empty when all badges are at 100%', async () => {
      mockStatisticService.getMyBadgesDetail.mockResolvedValue([
        { courseId: 1, courseTitle: 'A', badgeType: 'or', badgeLabel: 'Or', percentage: 100 },
      ])
      wrapper = mountView()
      await flushPromises()

      expect((wrapper.vm as unknown as ViewInstance).inProgressBadges).toHaveLength(0)
    })
  })

  // ── Computed: competences ──────────────────────────────────────────────────
  describe('acquiredCompetences', () => {
    it('should only include acquired competences', async () => {
      wrapper = mountView()
      await flushPromises()
      const vm = wrapper.vm as unknown as ViewInstance

      expect(vm.acquiredCompetences).toHaveLength(1)
      expect(vm.acquiredCompetences[0].id).toBe(1)
    })
  })

  describe('inProgressCompetences', () => {
    it('should only include non-acquired competences', async () => {
      wrapper = mountView()
      await flushPromises()
      const vm = wrapper.vm as unknown as ViewInstance

      expect(vm.inProgressCompetences).toHaveLength(2)
      expect(vm.inProgressCompetences.every((c) => !c.acquired)).toBe(true)
    })
  })

  describe('competencesByMatiere', () => {
    it('should group competences by matiere key', async () => {
      wrapper = mountView()
      await flushPromises()
      const vm = wrapper.vm as unknown as ViewInstance

      expect(Object.keys(vm.competencesByMatiere)).toEqual(['Maths', 'Géographie'])
      expect(vm.competencesByMatiere['Maths']).toHaveLength(2)
      expect(vm.competencesByMatiere['Géographie']).toHaveLength(1)
    })

    it('should return empty object when no competences', async () => {
      mockStatisticService.getMyCompetencesDetail.mockResolvedValue([])
      wrapper = mountView()
      await flushPromises()

      expect((wrapper.vm as unknown as ViewInstance).competencesByMatiere).toEqual({})
    })
  })

  // ── openCompetencePanels ───────────────────────────────────────────────────
  describe('openCompetencePanels', () => {
    it('should be initialized with all matiere keys after data loads', async () => {
      wrapper = mountView()
      await flushPromises()
      const vm = wrapper.vm as unknown as ViewInstance

      expect(vm.openCompetencePanels).toEqual(expect.arrayContaining(['Maths', 'Géographie']))
      expect(vm.openCompetencePanels).toHaveLength(2)
    })

    it('should be empty when competences list is empty', async () => {
      mockStatisticService.getMyCompetencesDetail.mockResolvedValue([])
      wrapper = mountView()
      await flushPromises()

      expect((wrapper.vm as unknown as ViewInstance).openCompetencePanels).toHaveLength(0)
    })
  })

  // ── goToCourse ─────────────────────────────────────────────────────────────
  describe('goToCourse', () => {
    it('should push the correct route', async () => {
      wrapper = mountView()
      await flushPromises()

      ;(wrapper.vm as unknown as ViewInstance).goToCourse(42)

      expect(mockRouter.push).toHaveBeenCalledWith('/course/42')
    })
  })

  // ── Empty states ───────────────────────────────────────────────────────────
  describe('Empty states', () => {
    it('should show badge empty state when no badges', async () => {
      mockStatisticService.getMyBadgesDetail.mockResolvedValue([])
      wrapper = mountView()
      await flushPromises()

      expect(wrapper.text()).toContain('Aucun badge pour le moment')
    })

    it('should show competences empty state when no competences', async () => {
      mockStatisticService.getMyCompetencesDetail.mockResolvedValue([])
      wrapper = mountView()
      await flushPromises()

      expect(wrapper.text()).toContain('Aucune compétence associée')
    })

    it('should not show badge empty state when badges exist', async () => {
      wrapper = mountView()
      await flushPromises()

      expect(wrapper.text()).not.toContain('Aucun badge pour le moment')
    })
  })

  // ── Tab switching ──────────────────────────────────────────────────────────
  describe('Tab switching', () => {
    it('should switch activeTab when VTabs emits update:modelValue', async () => {
      wrapper = mountView()
      await flushPromises()

      const tabs = wrapper.findComponent({ name: 'VTabs' })
      await tabs.vm.$emit('update:modelValue', 'competences')
      await flushPromises()

      expect((wrapper.vm as unknown as ViewInstance).activeTab).toBe('competences')
    })

    it('should switch activeTab when VWindow emits update:modelValue', async () => {
      wrapper = mountView()
      await flushPromises()

      const win = wrapper.findComponent({ name: 'VWindow' })
      await win.vm.$emit('update:modelValue', 'competences')
      await flushPromises()

      expect((wrapper.vm as unknown as ViewInstance).activeTab).toBe('competences')
    })
  })

  // ── VBtn click → goToCourse ────────────────────────────────────────────────
  describe('VBtn navigation', () => {
    it('should call goToCourse when a badge course button is clicked', async () => {
      wrapper = mountView()
      await flushPromises()

      const btn = wrapper.findComponent({ name: 'VBtn' })
      await btn.trigger('click')

      expect(mockRouter.push).toHaveBeenCalledWith(expect.stringContaining('/course/'))
    })

    it('should call goToCourse with competence courseId when competence btn is clicked', async () => {
      wrapper = mountView()
      await flushPromises()

      const allBtns = wrapper.findAllComponents({ name: 'VBtn' })
      await allBtns.at(-1).trigger('click')

      expect(mockRouter.push).toHaveBeenCalledWith(expect.stringContaining('/course/'))
    })
  })

  // ── Loading states ─────────────────────────────────────────────────────────
  describe('Loading states', () => {
    it('should show badge loading state before data resolves', () => {
      mockStatisticService.getMyBadgesDetail.mockResolvedValue(myBadgesDetailData)
      wrapper = mountView()

      expect((wrapper.vm as unknown as ViewInstance).badges).toHaveLength(0)
    })

    it('should show competence loading state before data resolves', () => {
      mockStatisticService.getMyCompetencesDetail.mockResolvedValue(myCompetencesDetailData)
      wrapper = mountView()

      expect((wrapper.vm as unknown as ViewInstance).competences).toHaveLength(0)
    })
  })

  // ── VExpansionPanels v-model update handlers ───────────────────────────────
  describe('VExpansionPanels v-model', () => {
    it('should update openPanels when badges VExpansionPanels emits update:modelValue', async () => {
      wrapper = mountView()
      await flushPromises()

      const panels = wrapper.findAllComponents({ name: 'VExpansionPanels' })
      await panels[0].vm.$emit('update:modelValue', ['acquired'])

      expect((wrapper.vm as unknown as ViewInstance).openPanels).toEqual(['acquired'])
    })

    it('should update openCompetencePanels when competences VExpansionPanels emits update:modelValue', async () => {
      wrapper = mountView()
      await flushPromises()

      const panels = wrapper.findAllComponents({ name: 'VExpansionPanels' })
      await panels.at(-1).vm.$emit('update:modelValue', ['Maths'])

      expect((wrapper.vm as unknown as ViewInstance).openCompetencePanels).toEqual(['Maths'])
    })
  })

  // ── Acquired badge VBtn click ───────────────────────────────────────────────
  describe('Acquired badge navigation', () => {
    it('should call goToCourse when an acquired badge course button is clicked', async () => {
      wrapper = mountView()
      await flushPromises()

      const allBtns = wrapper.findAllComponents({ name: 'VBtn' })
      await allBtns[2].trigger('click')

      expect(mockRouter.push).toHaveBeenCalledWith('/course/1')
    })
  })

  // ── Plural acquired competences ────────────────────────────────────────────
  describe('Pluralization', () => {
    it('should add plural "s" when acquiredCompetences count is > 1', async () => {
      mockStatisticService.getMyCompetencesDetail.mockResolvedValue([
        ...myCompetencesDetailData,
        { id: 4, nom: 'Dériver', niveau: 'avancé', courseId: 3, courseTitle: 'Analyse', matiere: 'Maths', acquired: true },
      ])
      wrapper = mountView()
      await flushPromises()

      expect((wrapper.vm as unknown as ViewInstance).acquiredCompetences).toHaveLength(2)
      expect(wrapper.text()).toContain('acquises')
    })

    it('should not add plural "s" when acquiredCompetences count is 1', async () => {
      wrapper = mountView()
      await flushPromises()

      expect((wrapper.vm as unknown as ViewInstance).acquiredCompetences).toHaveLength(1)
      expect(wrapper.text()).toContain('acquise')
    })
  })
})
