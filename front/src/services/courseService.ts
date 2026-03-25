import type { Course } from "@/types"
import { axiosInstance } from "@/utils"

export const courseService = {
  async getTopCourses(): Promise<Course[]> {
    const response = await axiosInstance.get('/api/cours')
    return response.data
  },

  async getCourseContentById(courseId: string): Promise<Course> {
    const response = await axiosInstance.get(`/api/cours/${courseId}`)
    return response.data
  },

  async retrieveMyCourses(): Promise<Course[]> {
    const response = await axiosInstance.get('/api/progression');
    return response.data;
  }
}