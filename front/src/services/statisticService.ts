import type { CompletionBySubjectPoint } from '@/types/component/chart/completion-by-subject.ts'
import type { ActiveStudentsPerClassPoint } from '@/types/component/chart/active-students-per-class.ts'
import type { BadgeDistributionPoint } from '@/types/component/chart/badge-distribution.ts'
import type { RegistrationsOverTimePoint } from '@/types/component/chart/registrations-over-time.ts'
import { axiosInstance } from '@/utils'

export const statisticService = {
  async getCompletionBySubject(): Promise<CompletionBySubjectPoint[]> {
    const response = await axiosInstance.get('/api/stats/completion-by-subject')
    return response.data
  },

  async getActiveStudentsPerClass(): Promise<ActiveStudentsPerClassPoint[]> {
    const response = await axiosInstance.get('/api/stats/active-students-per-class')
    return response.data
  },

  async getBadgeDistribution(): Promise<BadgeDistributionPoint[]> {
    const response = await axiosInstance.get('/api/stats/badge-distribution')
    return response.data
  },

  async getRegistrationsOverTime(): Promise<RegistrationsOverTimePoint[]> {
    const response = await axiosInstance.get('/api/stats/registrations')
    return response.data
  },
}
