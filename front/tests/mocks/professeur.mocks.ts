import type { User } from '../../src/types/user'

export const makeProfesseur = (): User =>
  ({ id: 2, nom: 'Prof', prenom: 'P', email: 'p@p.com', roles: ['ROLE_PROFESSEUR'] }) as any
