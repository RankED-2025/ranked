import { Course, CourseContent } from '../../src/types/course'

export const mockCourse: Course = {
	cours: {
		id: 1,
		professeur: {
			id: 10,
			nom: 'Martin',
			prenom: 'Alice',
		},
		titre: 'Mathématiques',
		description: 'Cours de mathématiques',
		matiere: {
			id: 2,
			libelle: 'Maths',
		},
	},
	pourcentage: 75,
	badge: {
		id: 3,
		type: 'gold',
		label: 'Avancé',
	},
}

export const mockCourseContent: CourseContent = {
	id: 1,
	titre: 'Mathématiques',
	description: 'Cours de mathématiques',
	professeur: {
		id: 10,
		nom: 'Martin',
		prenom: 'Alice',
	},
	matiere: {
		id: 2,
		libelle: 'Maths',
	},
	activites: [],
}