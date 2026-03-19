import { axiosInstance } from "@/utils"

export const courseService = {
  async getTopCourses(): Promise<any[]> {
    const response = await axiosInstance.get('/api/cours')
    return response.data
  },

  async getCourseContentById(courseId: string): Promise<any> {
    const response = await axiosInstance.get(`/api/cours/${courseId}`)
    return response.data
  },

  async retrieveMyCourses(): Promise<any[]> {
    const response = await axiosInstance.get('/api/progression');
    return response.data;
  }
}