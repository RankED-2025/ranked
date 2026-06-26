import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCourseStore } from '@/stores/courseStore'
import { courseService } from '@/services/courseService'
import { mockCourse, mockCourseContent } from '../mocks/cours'

const mockedCourseService = vi.mocked(courseService)

vi.mock('@/services/courseService', () => ({
	courseService: {
		retrieveMyCourses: vi.fn(),
		getCourseContentById: vi.fn(),
		getTopCourses: vi.fn(),
	},
}))

describe('useCourseStore', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
		vi.clearAllMocks()
	})

	it('initializes with the expected default state', () => {
		const store = useCourseStore()

		expect(store.myCourses).toBeNull()
		expect(store.loading).toBe(false)
	})

	it('returns an empty array from getMyCourses when no courses are stored', () => {
		const store = useCourseStore()

		expect(store.getMyCourses).toEqual([])
	})

	it('retrieveMyCourses stores the retrieved courses and returns them', async () => {
		const store = useCourseStore()

		mockedCourseService.retrieveMyCourses.mockResolvedValue([mockCourse])

		await expect(store.retrieveMyCourses()).resolves.toEqual([mockCourse])

		expect(mockedCourseService.retrieveMyCourses).toHaveBeenCalledTimes(1)
		expect(store.myCourses).toEqual([mockCourse])
		expect(store.getMyCourses).toEqual([mockCourse])
	})

	it('retrieveMyCourses returns an empty array when the service fails', async () => {
		const store = useCourseStore()
		const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => undefined)

		mockedCourseService.retrieveMyCourses.mockRejectedValue(new Error('request failed'))

		await expect(store.retrieveMyCourses()).resolves.toEqual([])

		expect(store.myCourses).toBeNull()
		expect(errorSpy).toHaveBeenCalled()

		errorSpy.mockRestore()
	})

	it('getCourseContent returns the course content by id', async () => {
		const store = useCourseStore()

		mockedCourseService.getCourseContentById.mockResolvedValue(mockCourseContent)

		await expect(store.getCourseContent('1')).resolves.toEqual(mockCourseContent)

		expect(mockedCourseService.getCourseContentById).toHaveBeenCalledWith('1')
	})

	it('getCourseContent returns null when the service fails', async () => {
		const store = useCourseStore()
		const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => undefined)

		mockedCourseService.getCourseContentById.mockRejectedValue(new Error('request failed'))

		await expect(store.getCourseContent('1')).resolves.toBeNull()

		expect(errorSpy).toHaveBeenCalled()

		errorSpy.mockRestore()
	})

	it('getTopCourses returns the top courses from the service', async () => {
		const store = useCourseStore()

		mockedCourseService.getTopCourses.mockResolvedValue([mockCourse])

		await expect(store.getTopCourses()).resolves.toEqual([mockCourse])

		expect(mockedCourseService.getTopCourses).toHaveBeenCalledTimes(1)
	})

	it('getTopCourses returns an empty array when the service fails', async () => {
		const store = useCourseStore()
		const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => undefined)

		mockedCourseService.getTopCourses.mockRejectedValue(new Error('request failed'))

		await expect(store.getTopCourses()).resolves.toEqual([])

		expect(errorSpy).toHaveBeenCalled()

		errorSpy.mockRestore()
	})
})
