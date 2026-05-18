import type { Difficulte, Matiere } from "./referentials"

export interface Course {
    cours: {
        id: number,
        title: string,
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

export interface AssignCourseData {
    cours_id: number
    classe_id: number
}

export interface CreatedCourse {
    id: number
    professeur: number
    matiere: number
    difficulte: Difficulte | null
}

export interface CourseActivity {
    id: number
    type: string
    ordre: number
    contenu: Contenu | null,
    qcm: QCM | null
}

export interface Contenu {
    id: number
    type: 'article' | 'video' | 'pdf' | 'image'
    url?: string
}

export interface QCM {
    id: number
    gainPts: number
}