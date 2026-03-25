import { defineStore } from 'pinia'
import type { Course, CourseContent } from '@/types/course'
import { courseService } from '@/services/courseService'

export interface CourseState {
  myCourses: Course[] | null,
  loading: boolean
}

export const useCourseStore = defineStore('course', {
  state: (): CourseState => {
    return {
      myCourses: null,
      loading: false,
    }
  },
  actions: {
    async retrieveMyCourses(): Promise<Course[]> {
      try {
        const courses = await courseService.retrieveMyCourses()
        this.myCourses = courses;
        return courses;
      } catch (error) {
        console.error('Erreur lors de la récupération des cours:', error)
        return []
      }
    },

    async getCourseContent(courseId: string): Promise<CourseContent | null> {
      try {
        const content = await courseService.getCourseContentById(courseId)
        return content;
      } catch (error) {
        console.error(`Erreur en récupérant le contenu du cours ${courseId}:`, error)
        return null
      }
    },

    async getTopCourses(): Promise<Course[]> {
      try {
        const courses = await courseService.getTopCourses()
        return courses
      } catch (error) {
        console.error('Erreur lors de la récupération des meilleurs cours:', error)
        return []
      }
    }
  },
  getters: {
    getMyCourses: (state) => state.myCourses ?? [],
  }
})
