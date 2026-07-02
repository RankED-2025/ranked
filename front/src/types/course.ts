import type { Difficulte, Matiere } from "./referentials"

export interface Course {
    cours: {
        id: number,
        titre: string,
        description: string,
        professeur: {
            id: number
            nom: string
            prenom: string
        },
        matiere: {
            id: number
            libelle: string
        }
    },
    pourcentage: number
    badge: {
        id: number
        type: string
        label: string
    }
}

export interface CourseContent {
    id: number
    title: string,
    description: string,
    professeur: {
        id: number
        nom: string
        prenom: string
    },
    matiere: Matiere | null,
    difficulte: Difficulte | null,
    activites: CourseActivity[]
}

export interface Classe {
    id: number
    nom: string
}

export interface ProfessorCourse {
    id: number,
    title: string,
    description: string,
    matiere: Matiere
    difficulte: Difficulte
}

export interface CreateCourseData {
    title: string,
    description: string,
    matiere_id: number
    difficulte_id: number
}

export interface CourseEditData {
    title: string,
    description: string,
    matiere_id: number
    difficulte_id: number
    activites?: CourseActivity[]
}

export interface AssignCourseData {
    cours_id: number
    classe_id: number
}

export interface CreatedCourse {
    id: number
    professeur: number
    matiere: number
    difficulte?: Difficulte
}

export interface CourseActivity {
    id: number
    type: string
    ordre: number
    completed: boolean
    contenu?: Contenu,
    qcm?: QCM
}

export interface Contenu {
    id: number
    type: 'article' | 'video' | 'pdf' | 'image'
    url?: string
}

export interface Reponse {
    id: number | null
    texte: string
    isCorrect: boolean
    // client-side only key for list rendering
    __uid?: string
}

export interface Question {
    id: number | null
    enonce: string
    reponses: Reponse[]
    // client-side only key for list rendering
    __uid?: string
}

export interface QCM {
    id: number | null
    gainPts: number
    questions?: Question[]
}

export interface ProfessorCourseContent {
    id: number
    title: string
    description: string
    matiere: Matiere | null
    difficulte: Difficulte | null
    activites: CourseActivity[]
}

export interface QuizQuestion {
    id: number
    enonce: string
    reponses: { id: number; texte: string }[]
}

export interface QuizToTake {
    id: number
    gainPts: number
    locked: boolean
    questions?: QuizQuestion[]
    result?: QuizResult
}

export interface QuizResult {
    score: number
    total: number
    earnedPts: number
    gainPts?: number
}

export interface ActiviteProgression {
    id: number
    activiteId: number
    completedAt: string | null
}
