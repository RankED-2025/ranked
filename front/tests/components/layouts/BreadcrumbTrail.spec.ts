import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import BreadcrumbTrail from '../../../src/components/layouts/BreadcrumbTrail.vue'
import { useUserStore } from '../../../src/stores/userStore'

const { routeMock, getRoutesMock, resolveMock } = vi.hoisted(() => {
	const routeMock = {
		name: 'course-content',
		path: '/my-courses/course-123',
	}

	const getRoutesMock = vi.fn()
	const resolveMock = vi.fn((location: { name: string }) => ({
		path: `/${location.name}`,
	}))

	return {
		routeMock,
		getRoutesMock,
		resolveMock,
	}
})

vi.mock('@/router', () => ({
	default: {
		getRoutes: getRoutesMock,
		resolve: resolveMock,
	},
}))

vi.mock('vue-router', () => ({
	RouterLink: {
		name: 'RouterLink',
		props: ['to'],
		template: '<a class="breadcrumb-link" :href="typeof to === \"string\" ? to : to?.path"><slot /></a>',
	},
	useRoute: () => routeMock,
}))

describe('BreadcrumbTrail.vue', () => {
	beforeEach(() => {
		resolveMock.mockClear()
		getRoutesMock.mockReset()
		setActivePinia(createPinia())
	})

	it('renders the breadcrumb chain from the router metadata and links the ancestors', () => {
		getRoutesMock.mockReturnValue([
			{
				name: 'home',
				meta: {
					breadcrumb: { label: 'Accueil' },
				},
			},
			{
				name: 'my-courses',
				meta: {
					breadcrumb: { label: 'Mes cours', parentName: 'home' },
				},
			},
			{
				name: 'course-content',
				meta: {
					breadcrumb: { label: 'Contenu du cours', parentName: 'my-courses' },
				},
			},
		])

		const wrapper = mount(BreadcrumbTrail, {
			global: {
				stubs: {
					RouterLink: {
						props: ['to'],
						template: '<a class="breadcrumb-link" :href="to"><slot /></a>',
					},
				},
			},
		})

		expect(wrapper.text()).toContain('Accueil')
		expect(wrapper.text()).toContain('Mes cours')
		expect(wrapper.text()).toContain('Contenu du cours')

		const links = wrapper.findAll('a.breadcrumb-link')
		expect(links).toHaveLength(2)
		expect(links[0].attributes('href')).toBe('/home')
		expect(links[0].text()).toBe('Accueil')
		expect(links[1].attributes('href')).toBe('/my-courses')
		expect(links[1].text()).toBe('Mes cours')
		expect(wrapper.find('.breadcrumb-current').text()).toBe('Contenu du cours')
		expect(resolveMock).toHaveBeenCalledWith({ name: 'home' })
		expect(resolveMock).toHaveBeenCalledWith({ name: 'my-courses' })
	})

	it('links back to the student course list when a student views a course', () => {
		const userStore = useUserStore()
		userStore.user = { roles: ['ROLE_ELEVE'] } as never

		getRoutesMock.mockReturnValue([
			{
				name: 'home',
				meta: {
					breadcrumb: { label: 'Accueil' },
				},
			},
			{
				name: 'my-courses',
				meta: {
					breadcrumb: { label: 'Mes cours', parentName: 'home' },
				},
			},
			{
				name: 'professor-my-courses',
				meta: {
					breadcrumb: { label: 'Cours professeur', parentName: 'home' },
				},
			},
			{
				name: 'course-content',
				meta: {
					breadcrumb: { label: 'Contenu du cours', parentName: 'my-courses' },
				},
			},
		])

		const wrapper = mount(BreadcrumbTrail, {
			global: {
				stubs: {
					RouterLink: {
						props: ['to'],
						template: '<a class="breadcrumb-link" :href="to"><slot /></a>',
					},
				},
			},
		})

		const links = wrapper.findAll('a.breadcrumb-link')
		expect(links).toHaveLength(2)
		expect(links[1].attributes('href')).toBe('/my-courses')
		expect(links[1].text()).toBe('Mes cours')
		expect(resolveMock).toHaveBeenCalledWith({ name: 'my-courses' })
	})

	it('links back to the professor course list when a professor views a course', () => {
		const userStore = useUserStore()
		userStore.user = { roles: ['ROLE_PROFESSEUR'] } as never

		getRoutesMock.mockReturnValue([
			{
				name: 'home',
				meta: {
					breadcrumb: { label: 'Accueil' },
				},
			},
			{
				name: 'my-courses',
				meta: {
					breadcrumb: { label: 'Mes cours', parentName: 'home' },
				},
			},
			{
				name: 'professor-my-courses',
				meta: {
					breadcrumb: { label: 'Cours professeur', parentName: 'home' },
				},
			},
			{
				name: 'course-content',
				meta: {
					breadcrumb: { label: 'Contenu du cours', parentName: 'my-courses' },
				},
			},
		])

		const wrapper = mount(BreadcrumbTrail, {
			global: {
				stubs: {
					RouterLink: {
						props: ['to'],
						template: '<a class="breadcrumb-link" :href="to"><slot /></a>',
					},
				},
			},
		})

		const links = wrapper.findAll('a.breadcrumb-link')
		expect(links).toHaveLength(2)
		expect(links[1].attributes('href')).toBe('/professor-my-courses')
		expect(links[1].text()).toBe('Cours professeur')
		expect(resolveMock).toHaveBeenCalledWith({ name: 'professor-my-courses' })
	})
})
