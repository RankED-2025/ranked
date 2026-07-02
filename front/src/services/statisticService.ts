import type {
  CompletionBySubjectPoint,
  ActiveStudentsPerClassPoint,
  BadgeDistributionPoint,
  RegistrationsOverTimePoint,
  MyProgressionPoint,
  MyCompetencePoint,
  MyQuizScorePoint,
  MyBadgePoint,
  MyBadgeDetail,
  MyCompetenceDetail,
  MyClassRank,
  BestStudent,
} from '@/types'
import { axiosInstance } from '@/utils'

export const statisticService = {
  // — global —
  async getCompletionBySubject(): Promise<CompletionBySubjectPoint[]> {
    return (await axiosInstance.get('/api/stats/completion-by-subject')).data
  },

  async getActiveStudentsPerClass(): Promise<ActiveStudentsPerClassPoint[]> {
    return (await axiosInstance.get('/api/stats/active-students-per-class')).data
  },

  async getBadgeDistribution(): Promise<BadgeDistributionPoint[]> {
    return (await axiosInstance.get('/api/stats/badge-distribution')).data
  },

  async getRegistrationsOverTime(): Promise<RegistrationsOverTimePoint[]> {
    return (await axiosInstance.get('/api/stats/registrations')).data
  },

  // — personal (eleve only) —
  async getMyProgressions(): Promise<MyProgressionPoint[]> {
    return (await axiosInstance.get('/api/my-stats/progressions')).data
  },

  async getMyCompetences(): Promise<MyCompetencePoint[]> {
    return (await axiosInstance.get('/api/my-stats/competences')).data
  },

  async getMyQuizScores(): Promise<MyQuizScorePoint[]> {
    return (await axiosInstance.get('/api/my-stats/quiz-scores')).data
  },

  async getMyBadges(): Promise<MyBadgePoint[]> {
    return (await axiosInstance.get('/api/my-stats/badges')).data
  },

  async getMyClassRank(): Promise<MyClassRank> {
    return (await axiosInstance.get('/api/my-stats/class-rank')).data
  },

  async getMyBadgesDetail(): Promise<MyBadgeDetail[]> {
    return (await axiosInstance.get('/api/my-stats/badges-detail')).data
  },

  async getMyCompetencesDetail(): Promise<MyCompetenceDetail[]> {
    return (await axiosInstance.get('/api/my-stats/competences-detail')).data
  },

  // — professor —
  async getBestStudents(classeId: number, limit: number = 5): Promise<BestStudent[]> {
    return (await axiosInstance.get(`/api/stats/best-students/${classeId}/${limit}`)).data
  },
}
