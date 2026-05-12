import type { Course, CourseContent, ProfessorCourse, Classe, ClassDetail, CreateCourseData, AssignCourseData, CreatedCourse } from "@/types"
import { axiosInstance } from "@/utils"

export const courseService = {
  async getTopCourses(): Promise<Course[]> {
    const response = await axiosInstance.get('/api/cours')
    return response.data
  },

  async getCourseContentById(courseId: string): Promise<CourseContent> {
    const response = await axiosInstance.get(`/api/cours/${courseId}`)
    return response.data
  },

  async retrieveMyCourses(): Promise<Course[]> {
    const response = await axiosInstance.get('/api/progression');
    return response.data;
  },

  async getProfessorCourses(): Promise<ProfessorCourse[]> {
    const response = await axiosInstance.get('/api/professor/courses')
    return response.data
  },

  async getProfessorClasses(): Promise<Classe[]> {
    const response = await axiosInstance.get('/api/professor/classes')
    return response.data
  },

  async getProfessorClassDetail(id: number): Promise<ClassDetail> {
    const response = await axiosInstance.get(`/api/professor/classes/${id}`)
    return response.data
  },

  async createCourse(data: CreateCourseData): Promise<CreatedCourse> {
    const response = await axiosInstance.post('/api/professor/courses', data)
    return response.data
  },

  async assignCourseToClass(data: AssignCourseData): Promise<{ message: string }> {
    const response = await axiosInstance.post('/api/professor/courses/assign', data)
    return response.data
  },

  async editCourse(courseId: number | string, data: any): Promise<{ message: string, id: number }> {
    const response = await axiosInstance.post(`/api/professor/courses/edit/${courseId}`, data)
    return response.data
  },

  async getTopCoursesByAvg(top: number = 5): Promise<Array<Course & { average: number }>> {
    return (await axiosInstance.get(`/api/cours/top?top=${top}`)).data
  }
}
