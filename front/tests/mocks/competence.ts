import type { MyCompetencePoint } from '../../src/types'
import type { MyCompetenceDetail } from '../../src/types'

export const myCompetencesData: MyCompetencePoint[] = [
  { matiere: 'Maths', percentage: 70 },
  { matiere: 'Info', percentage: 90 },
]

export const myCompetencesPoints: MyCompetencePoint[] = [
  { matiere: 'Maths', percentage: 80 },
  { matiere: 'Physique', percentage: 65 },
  { matiere: 'Chimie', percentage: 90 },
]

export const myCompetencesDetailData: MyCompetenceDetail[] = [
  { id: 1, nom: 'Résoudre des équations', niveau: 'débutant', courseId: 1, courseTitle: 'Algèbre', matiere: 'Maths', acquired: true },
  { id: 2, nom: 'Lire une carte', niveau: 'intermédiaire', courseId: 2, courseTitle: 'Géo I', matiere: 'Géographie', acquired: false },
  { id: 3, nom: 'Calculer des proportions', niveau: 'avancé', courseId: 1, courseTitle: 'Algèbre', matiere: 'Maths', acquired: false },
]
