import { defineStore } from 'pinia'
import type { Course, CourseContent } from '@/types/course'
import { courseService } from '@/services/courseService'

export interface CourseState {
  myCourses: Course[] | null,
  loading: boolean,
  error: string | null
}

export const useCourseStore = defineStore('course', {
  state: (): CourseState => {
    return {
      myCourses: null,
      loading: false,
      error: null,
    }
  },
  actions: {
    async retrieveMyCourses(): Promise<Course[]> {
      try {
        const courses = await courseService.retrieveMyCourses()
        this.myCourses = courses;
        return courses;
      } catch {
        this.error = 'Impossible de récupérer vos cours.'
        return []
      }
    },

    async getCourseContent(courseId: string): Promise<CourseContent | null> {
      try {
        const content = await courseService.getCourseContentById(courseId)
        return content;
      } catch {
        this.error = `Impossible de récupérer le contenu du cours.`
        return null
      }
    },

    async getTopCourses(): Promise<Course[]> {
      try {
        const courses = await courseService.getTopCourses()
        return courses
      } catch {
        this.error = 'Impossible de récupérer les meilleurs cours.'
        return []
      }
    },

    async updateProgression(courseId: string, percentage: number): Promise<boolean> {
      try {
        await courseService.updateProgression(courseId, percentage)
        return true
      } catch {
        this.error = 'Impossible de mettre à jour la progression du cours.'
        return false
      }
    },

    async updateActiviteProgression(activiteId: number, completed: boolean): Promise<boolean> {
      try {
        await courseService.updateActiviteProgression(activiteId, completed)
        return true
      } catch {
        this.error = 'Impossible de mettre à jour la progression de l\'activité.'
        return false
      }
    }
  },
  getters: {
    getMyCourses: (state) => state.myCourses ?? [],
    getError: (state) => state.error,
  }
})
