import type { Matiere, Difficulte } from '@/types'
import { axiosInstance } from '@/utils'

export const referentielService = {
  async getMatieres(): Promise<Matiere[]> {
    const response = await axiosInstance.get('/api/referentiels/matieres')
    return response.data
  },
  async getDifficultes(): Promise<Difficulte[]> {
    const response = await axiosInstance.get('/api/referentiels/difficultes')
    return response.data
  }
}