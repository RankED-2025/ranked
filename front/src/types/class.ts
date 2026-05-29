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