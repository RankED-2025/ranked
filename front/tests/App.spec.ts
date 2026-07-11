import App from '../src/App.vue';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { useUserStore } from '../src/stores/userStore.ts';
import { createPinia, setActivePinia } from 'pinia';
import { isAdmin } from '@/utils/roles'
import { authService } from '@/services/authService'

const pushMock = vi.fn()

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal<typeof import('vue-router')>()
  
  return {
    ...actual,
    useRouter: () => ({
      push: pushMock,
    }),
    useRoute: () => ({
      name: 'Home',
      path: '/',
      params: {},
      query: {},
    }),
    RouterView: {
      name: 'RouterView',
      template: '<div data-testid="router-view" />',
    },
  }
})

vi.mock('@/utils/roles', () => ({
  getUserRoleLabel: (roles: string[]) => `label-${roles.join(',')}`,
  isProfesseur: vi.fn().mockReturnValue(false),
  isAdmin: vi.fn().mockReturnValue(false),
}))

vi.mock('@/components/loading/LoadingModal.vue', () => ({
  default: {
    name: 'LoadingModal',
    props: ['message', 'size'],
    template: '<div data-testid="loading-modal">{{ message }}</div>',
  },
}))

vi.mock('@/services/authService', () => ({
  authService: {
    getAdminSsoUrl: vi.fn(),
  },
}))

const globalStubs = {
  'v-app': { template: '<div><slot /></div>' },
  'v-app-bar': {
    template: '<div id="app-bar" @click="$emit(\'click\', $event)"><slot name="prepend" /><slot /></div>',
  },
  'v-toolbar-title': { template: '<div><slot /></div>' },
  'v-spacer': { template: '<div />' },
  'v-chip': { template: '<div class="v-chip"><slot /></div>' },
  'v-btn': {
    template: '<button @click="$emit(\'click\', $event)"><slot /></button>',
  },
  'v-main': { template: '<div><slot /></div>' },
}

function mountApp(userState: {
  user?: { email: string; roles: string[] | null } | null
  token?: string | null
  refreshToken?: string | null
  loading?: boolean
  initializing?: boolean
} = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)

  const userStore = useUserStore()
  userStore.$patch({
    user: null,
    token: null,
    refreshToken: null,
    loading: false,
    initializing: false,
    ...userState,
  })

  const wrapper = mount(App, {
    global: {
      plugins: [pinia],
      mocks: {
        $router: {
          push: pushMock,
        },
      },
      stubs: globalStubs,
    },
  })

  return { wrapper, userStore }
}

describe('App.vue', () => {
  const originalLocation = window.location

  beforeEach(() => {
    pushMock.mockClear()
    vi.mocked(isAdmin).mockReturnValue(false)
    vi.mocked(authService.getAdminSsoUrl).mockReset()

    Object.defineProperty(window, 'location', {
      configurable: true,
      writable: true,
      value: { ...originalLocation, href: 'http://localhost/' },
    })
  })

  afterEach(() => {
    Object.defineProperty(window, 'location', {
      configurable: true,
      writable: true,
      value: originalLocation,
    })
  })

  it('does not render the app-bar when user is not authenticated', () => {
    const { wrapper } = mountApp({ user: null, token: null })
    expect(wrapper.find('#app-bar').exists()).toBe(false)
  })

  it('renders the app-bar when user is authenticated', () => {
    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'test@example.com', roles: ['ADMIN'] },
    })
    expect(wrapper.find('#app-bar').exists()).toBe(true)
  })

  it('displays the user email and role label', () => {
    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'john.doe@example.com', roles: ['ADMIN'] },
    })

    expect(wrapper.text()).toContain('john.doe@example.com')
    expect(wrapper.text()).toContain('label-ADMIN')
  })

  it('displays an empty role label when user has no roles', () => {
    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'john.doe@example.com', roles: null },
    })

    expect(wrapper.text()).not.toContain('label-')
  })

  it('calls logout and redirects to /login when clicking the logout button', async () => {
    const { wrapper, userStore } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'a@a.com', roles: ['USER'] },
    })

    userStore.logout = vi.fn().mockResolvedValue(undefined)

    const logoutBtn = wrapper.find('#logout-button')
    expect(logoutBtn.exists()).toBeTruthy()

    await logoutBtn!.trigger('click')

    expect(userStore.logout).toHaveBeenCalled()
    expect(pushMock).toHaveBeenCalledWith('/login')
  })

  it('navigates to home when clicking the home button', async () => {
    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'a@a.com', roles: ['USER'] },
    })

    const homeBtn = wrapper.find('.nav-brand')
    expect(homeBtn.exists()).toBe(true)

    await homeBtn.trigger('click')

    expect(pushMock).toHaveBeenCalledWith('/')
  })

  it('navigates to home when clicking the brand button in the navbar', async () => {
    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'a@a.com', roles: ['USER'] },
    })

    await wrapper.find('.nav-brand').trigger('click')

    expect(pushMock).toHaveBeenCalledWith('/')
  })

  it('shows the LoadingModal when isInitializing is true', () => {
    const { wrapper } = mountApp({ initializing: true })

    expect(wrapper.find('#loading-modal').exists()).toBe(true)
  })

  it('shows the RouterView when isInitializing is false', () => {
    const { wrapper } = mountApp({ initializing: false })

    expect(wrapper.find('#loading-modal').exists()).toBe(false)
  })

  it('does not show the LoadingModal for in-flight login/register requests', () => {
    const { wrapper } = mountApp({ loading: true, initializing: false })

    expect(wrapper.find('#loading-modal').exists()).toBe(false)
  })

  it('does not render the "Panel admin" button when user is not admin', () => {
    vi.mocked(isAdmin).mockReturnValue(false)

    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'a@a.com', roles: ['ROLE_PROFESSEUR'] },
    })

    expect(wrapper.find('#admin-panel-button').exists()).toBe(false)
  })

  it('renders the "Panel admin" button when user is admin', () => {
    vi.mocked(isAdmin).mockReturnValue(true)

    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'a@a.com', roles: ['ROLE_ADMIN'] },
    })

    expect(wrapper.find('#admin-panel-button').exists()).toBe(true)
  })

  it('navigates to the admin SSO url when clicking the "Panel admin" button', async () => {
    vi.mocked(isAdmin).mockReturnValue(true)
    vi.mocked(authService.getAdminSsoUrl).mockResolvedValue('https://back.rank-ed.fr/admin/sso/abc123')

    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'a@a.com', roles: ['ROLE_ADMIN'] },
    })

    await wrapper.find('#admin-panel-button').trigger('click')
    await vi.waitUntil(() => window.location.href === 'https://back.rank-ed.fr/admin/sso/abc123')

    expect(authService.getAdminSsoUrl).toHaveBeenCalled()
    expect(window.location.href).toBe('https://back.rank-ed.fr/admin/sso/abc123')
  })
})