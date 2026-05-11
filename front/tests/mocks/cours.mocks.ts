import type { Course } from '../../src/types/course'
import type { MostCompletedCourseSinglePoint } from '../../src/types/component/chart/most-completed-courses'

export const makeCourse = (titre: string): Course => ({
  cours: {
    id: 1,
    titre,
    description: 'desc',
    professeur: { id: 1, nom: 'Dupont', prenom: 'Jean' },
    matiere: { id: 1, libelle: 'Maths' },
  },
  pourcentage: 100,
  badge: { id: 1, type: 'bronze', label: 'Bronze' },
})

export const topCoursesData = [
  { ...makeCourse('Algèbre'), average: 85 },
  { ...makeCourse('Géométrie'), average: 72 },
]

export const mostCompletedCoursesPoints: MostCompletedCourseSinglePoint[] = [
  { percent: 85, course: makeCourse('Algèbre') },
  { percent: 72, course: makeCourse('Géométrie') },
]
