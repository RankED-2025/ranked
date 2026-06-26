import type { BestStudent } from '../../src/types'

export const bestStudentsData: BestStudent[] = [
  {
    rank: 1,
    name: 'Martin',
    firstname: 'Alice',
    average: 92,
    completedCourses: 8,
    totalCourses: 10,
    topSubject: 'Maths',
  },
  {
    rank: 2,
    name: 'Dupont',
    firstname: 'Bob',
    average: 65,
    completedCourses: 7,
    totalCourses: 10,
    topSubject: 'Physique',
  },
  {
    rank: 3,
    name: 'Leroy',
    firstname: 'Clara',
    average: 78,
    completedCourses: 6,
    totalCourses: 10,
    topSubject: null,
  },
  {
    rank: 4,
    name: 'Petit',
    firstname: 'David',
    average: 40,
    completedCourses: 4,
    totalCourses: 10,
    topSubject: 'Info',
  },
]
