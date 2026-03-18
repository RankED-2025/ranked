export interface Course {
    cours: {
        id: number
        professeur: {
            id: number
            name: string
            firstName: string
        },
        matiere: {
            id: number
            libelle: string
        }
    },
    percentage: number
    badge: {
        id: number
        type: string
        label: string
    }
}

export interface CourseContent {
    id: number
    professeur: {
        id: number
        name: string
        firstName: string
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
    contenu: any
    qcm: {
        id: number
        gainPts: number
    } | null
}