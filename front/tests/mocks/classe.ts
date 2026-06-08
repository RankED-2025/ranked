import type { ActiveStudentsPerClassPoint } from '../../src/types'
import type { ClassDetail } from '../../src/types'

export const activeStudentsData: ActiveStudentsPerClassPoint[] = [
  { classe: '3A', count: 20 },
  { classe: '3B', count: 18 },
]

export const activeStudentsPoints: ActiveStudentsPerClassPoint[] = [
  { classe: '3A', count: 18 },
  { classe: '3B', count: 24 },
]

// ── ClassDetail fixtures ─────────────────────────────────────────────────────

export const classDetail: ClassDetail = {
  id: 42,
  nom: 'Terminale A',
  students: [
    {
      id: 1,
      name: 'Martin',
      firstname: 'Alice',
      progressions: [
        {
          cours: { id: 10, professeur: 1, matiere: { id: 1, libelle: 'Maths' } },
          percentage: 80,
          badge: null,
        },
      ],
    },
  ],
}

// No progressions → assignedCourses = []
export const classDetailNoCourses: ClassDetail = {
  id: 42,
  nom: 'Terminale A',
  students: [
    { id: 1, name: 'Martin', firstname: 'Alice', progressions: [] },
  ],
}

// Two students on the same course; second has no progression → gets null percentage
export const classDetailMultiStudents: ClassDetail = {
  id: 42,
  nom: 'Terminale A',
  students: [
    {
      id: 1,
      name: 'Martin',
      firstname: 'Alice',
      progressions: [
        {
          cours: { id: 10, professeur: 1, matiere: { id: 1, libelle: 'Maths' } },
          percentage: 95,
          badge: { id: 1, type: 'gold', label: 'Or' },
        },
      ],
    },
    {
      id: 2,
      name: 'Dupont',
      firstname: 'Bob',
      progressions: [],
    },
  ],
}

// Student whose only progression has cours: null → no assignedCourses
export const classDetailNullCours: ClassDetail = {
  id: 42,
  nom: 'Terminale A',
  students: [
    {
      id: 1,
      name: 'Martin',
      firstname: 'Alice',
      progressions: [{ cours: null, percentage: 80, badge: null }],
    },
  ],
}

// Student with undefined percentage → coalesces to null inside studentProgressionsByCourse
export const classDetailNullPercentage: ClassDetail = {
  id: 42,
  nom: 'Terminale A',
  students: [
    {
      id: 1,
      name: 'Martin',
      firstname: 'Alice',
      progressions: [
        {
          cours: { id: 10, professeur: 1, matiere: { id: 1, libelle: 'Maths' } },
          percentage: undefined as number, // that's really cursed lmao
          badge: null,
        },
      ],
    },
  ],
}

export const classDetailSilverBadge: ClassDetail = {
  id: 42,
  nom: 'Terminale A',
  students: [
    {
      id: 1,
      name: 'Martin',
      firstname: 'Alice',
      progressions: [
        {
          cours: { id: 10, professeur: 1, matiere: { id: 1, libelle: 'Maths' } },
          percentage: 70,
          badge: { id: 2, type: 'silver', label: 'Argent' },
        },
      ],
    },
  ],
}

export const classDetailBronzeBadge: ClassDetail = {
  id: 42,
  nom: 'Terminale A',
  students: [
    {
      id: 1,
      name: 'Martin',
      firstname: 'Alice',
      progressions: [
        {
          cours: { id: 10, professeur: 1, matiere: { id: 1, libelle: 'Maths' } },
          percentage: 50,
          badge: { id: 3, type: 'bronze', label: 'Bronze' },
        },
      ],
    },
  ],
}
