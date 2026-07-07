import App from '../src/App.vue';
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { useUserStore } from '../src/stores/userStore.ts';
import { createPinia, setActivePinia } from 'pinia';

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
}))

vi.mock('@/components/loading/LoadingModal.vue', () => ({
  default: {
    name: 'LoadingModal',
    props: ['message', 'size'],
    template: '<div data-testid="loading-modal">{{ message }}</div>',
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
} = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)

  const userStore = useUserStore()
  userStore.$patch({
    user: null,
    token: null,
    refreshToken: null,
    loading: false,
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
  beforeEach(() => {
    pushMock.mockClear()
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

    const logoutBtn = wrapper.findAll('button').find((b) => b.text().includes('Déconnexion'))
    expect(logoutBtn).toBeTruthy()

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

    const homeBtn = wrapper.find('.home-button')
    expect(homeBtn.exists()).toBe(true)

    await homeBtn.trigger('click')

    expect(pushMock).toHaveBeenCalledWith('/')
  })

  it('navigates to home when clicking the app-bar itself', async () => {
    const { wrapper } = mountApp({
      token: 'ABCDEFGH',
      refreshToken: '12345678',
      loading: false,
      user: { email: 'a@a.com', roles: ['USER'] },
    })

    await wrapper.find('#app-bar').trigger('click')

    expect(pushMock).toHaveBeenCalledWith('/')
  })

  it('shows the LoadingModal when isLoading is true', () => {
    const { wrapper } = mountApp({ loading: true })

    expect(wrapper.find('#loading-modal').exists()).toBe(true)
  })

  it('shows the RouterView when isLoading is false', () => {
    const { wrapper } = mountApp({ loading: false })

    expect(wrapper.find('#loading-modal').exists()).toBe(false)
  })
})