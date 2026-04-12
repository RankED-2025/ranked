export interface Course {
    cours: {
        id: number
        professeur: {
            id: number
            nom: string
            prenom: string
        },
        titre: string,
        description: string,
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
    titre: string,
    description: string,
    professeur: {
        id: number
        nom: string
        prenom: string
    }
    matiere: {
        id: number
        libelle: string
    },
    activites: CourseActivity[]
}

export interface Matiere {
    id: number
    libelle: string
}

export interface Difficulte {
    id: number
    label: string
}

export interface Classe {
    id: number
    nom: string
}

export interface ClassProgression {
    cours: {
        id: number
        professeur: number
        matiere: { id: number; libelle: string }
    } | null
    percentage: number
    badge: { id: number; type: string; label: string } | null
}

export interface ClassStudent {
    id: number
    name: string
    firstname: string
    progressions: ClassProgression[]
}

export interface ClassDetail {
    id: number
    nom: string
    students: ClassStudent[]
}

export interface ProfessorCourse {
    id: number
    matiere: Matiere
    difficulte: Difficulte | null
}

export interface CreateCourseData {
    matiere_id: number
    difficulte_id?: number
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
    contenu: {
      id: number
      type: string
      url?: string
    } | null,
    qcm: {
        id: number
        gainPts: number
    } | null
}
