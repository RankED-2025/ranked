import type { Course, CourseContent } from '../../src/types/course'
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

export const mockCourse: Course = {
  cours: {
    id: 1,
    professeur: { id: 10, nom: 'Martin', prenom: 'Alice' },
    titre: 'Mathématiques',
    description: 'Cours de mathématiques',
    matiere: { id: 2, libelle: 'Maths' },
  },
  pourcentage: 75,
  badge: { id: 3, type: 'gold', label: 'Avancé' },
}

export const mockCourseContent: CourseContent = {
  id: 1,
  titre: 'Mathématiques',
  description: 'Cours de mathématiques',
  professeur: { id: 10, nom: 'Martin', prenom: 'Alice' },
  matiere: { id: 2, libelle: 'Maths' },
  activites: [],
}
