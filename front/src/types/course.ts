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
