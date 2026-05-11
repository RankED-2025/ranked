import type { User } from '../../src/types/user'
import type { RegistrationsOverTimePoint } from '../../src/types/component/chart/registrations-over-time'
import type { MyClassRank } from '../../src/types/component/chart/my-class-rank'

export const makeStudent = (): User =>
  ({ id: 1, nom: 'Eleve', prenom: 'E', email: 'e@e.com', roles: ['ROLE_ELEVE'] }) as any

export const registrationsData: RegistrationsOverTimePoint[] = [
  { week: '2024-W01', count: 3 },
  { week: '2024-W02', count: 7 },
]

export const registrationsPoints: RegistrationsOverTimePoint[] = [
  { week: '2024-W01', count: 10 },
  { week: '2024-W02', count: 15 },
  { week: '2024-W03', count: 8 },
]

export const myClassRankData: MyClassRank = {
  rank: 3,
  total: 25,
  myAverage: 78,
  percentile: 88,
}

export const myClassRankDefault: MyClassRank = {
  rank: 3,
  total: 25,
  myAverage: 78,
  percentile: 88,
}
