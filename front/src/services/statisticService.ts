import type { CompletionBySubjectPoint } from '@/types/component/chart/completion-by-subject.ts'
import type { ActiveStudentsPerClassPoint } from '@/types/component/chart/active-students-per-class.ts'
import type { BadgeDistributionPoint } from '@/types/component/chart/badge-distribution.ts'
import type { RegistrationsOverTimePoint } from '@/types/component/chart/registrations-over-time.ts'
import type { MyProgressionPoint } from '@/types/component/chart/my-progression.ts'
import type { MyCompetencePoint } from '@/types/component/chart/my-competences.ts'
import type { MyQuizScorePoint } from '@/types/component/chart/my-quiz-scores.ts'
import type { MyBadgePoint } from '@/types/component/chart/my-badges.ts'
import type { MyClassRank } from '@/types/component/chart/my-class-rank.ts'
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
}
