// App.spec.ts
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { useUserStore } from '@/stores/userStore'
import App from '@/App.vue'

// --- Mocks ---

const pushMock = vi.fn()

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: pushMock,
  }),
  RouterView: {
    name: 'RouterView',
    template: '<div data-testid="router-view" />',
  },
}))

vi.mock('@/utils/roles', () => ({
  getUserRoleLabel: (roles: string[]) => `label-${roles.join(',')}`,
}))

vi.mock('@/components/loading/LoadingModal.vue', () => ({
  default: {
    name: 'LoadingModal',
    props: ['message', 'size'],
    template: '<div data-testid="loading-modal">{{ message }}</div>',
  },
}))

// Stub Vuetify components to avoid needing a full Vuetify instance
const globalStubs = {
  'v-app': { template: '<div><slot /></div>' },
  'v-app-bar': {
    template: '<div data-testid="app-bar" @click="$emit(\'click\')"><slot name="prepend" /><slot /></div>',
  },
  'v-toolbar-title': { template: '<div><slot /></div>' },
  'v-spacer': { template: '<div />' },
  'v-chip': { template: '<div class="v-chip"><slot /></div>' },
  'v-btn': {
    template: '<button @click="$emit(\'click\')"><slot /></button>',
  },
  'v-main': { template: '<div><slot /></div>' },
}

function mountApp(userState: Partial<ReturnType<typeof useUserStore>> = {}) {
  const wrapper = mount(App, {
    global: {
      plugins: [
        createTestingPinia({
          createSpy: vi.fn,
          stubActions: false,
          initialState: {
            user: {
              isAuthenticated: false,
              isLoading: false,
              user: null,
              ...userState,
            },
          },
        }),
      ],
      stubs: globalStubs,
    },
  })

  const userStore = useUserStore()
  return { wrapper, userStore }
}

describe('App.vue', () => {
  beforeEach(() => {
    pushMock.mockClear()
  })

  it('does not render the app-bar when user is not authenticated', () => {
    const { wrapper } = mountApp({ isAuthenticated: false })
    expect(wrapper.find('[data-testid="app-bar"]').exists()).toBe(false)
  })

  it('renders the app-bar when user is authenticated', () => {
    const { wrapper } = mountApp({
      isAuthenticated: true,
      user: { email: 'test@example.com', roles: ['ADMIN'] },
    })
    expect(wrapper.find('[data-testid="app-bar"]').exists()).toBe(true)
  })

  it('displays the user email and role label', () => {
    const { wrapper } = mountApp({
      isAuthenticated: true,
      user: { email: 'john.doe@example.com', roles: ['ADMIN'] },
    })

    expect(wrapper.text()).toContain('john.doe@example.com')
    expect(wrapper.text()).toContain('label-ADMIN')
  })

  it('displays an empty role label when user has no roles', () => {
    const { wrapper } = mountApp({
      isAuthenticated: true,
      user: { email: 'john.doe@example.com', roles: null },
    })

    expect(wrapper.text()).not.toContain('label-')
  })

  it('calls logout and redirects to /login when clicking the logout button', async () => {
    const { wrapper, userStore } = mountApp({
      isAuthenticated: true,
      user: { email: 'a@a.com', roles: ['USER'] },
    })

    // logout action mocked by createTestingPinia
    userStore.logout = vi.fn().mockResolvedValue(undefined)

    const logoutBtn = wrapper.findAll('button').find((b) => b.text().includes('Déconnexion'))
    expect(logoutBtn).toBeTruthy()

    await logoutBtn!.trigger('click')

    expect(userStore.logout).toHaveBeenCalledOnce()
    expect(pushMock).toHaveBeenCalledWith('/login')
  })

  it('navigates to home when clicking the home button', async () => {
    const { wrapper } = mountApp({
      isAuthenticated: true,
      user: { email: 'a@a.com', roles: ['USER'] },
    })

    const homeBtn = wrapper.find('.home-button')
    expect(homeBtn.exists()).toBe(true)

    await homeBtn.trigger('click')

    expect(pushMock).toHaveBeenCalledWith('/')
  })

  it('navigates to home when clicking the app-bar itself', async () => {
    const { wrapper } = mountApp({
      isAuthenticated: true,
      user: { email: 'a@a.com', roles: ['USER'] },
    })

    await wrapper.find('[data-testid="app-bar"]').trigger('click')

    expect(pushMock).toHaveBeenCalledWith('/')
  })

  it('shows the LoadingModal when isLoading is true', () => {
    const { wrapper } = mountApp({ isLoading: true })

    expect(wrapper.find('[data-testid="loading-modal"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="router-view"]').exists()).toBe(false)
  })

  it('shows the RouterView when isLoading is false', () => {
    const { wrapper } = mountApp({ isLoading: false })

    expect(wrapper.find('[data-testid="loading-modal"]').exists()).toBe(false)
    expect(wrapper.find('[data-testid="router-view"]').exists()).toBe(true)
  })
})