import { describe, expect, it } from 'vitest'
import { ROLES } from '@/constants/roles'
import {
	hasAllRoles,
	hasAnyRole,
	hasRole,
	isAdmin,
	isEleve,
	isProfesseur,
	getPrimaryRole,
	getRoleLabel,
	getUserRoleLabel,
} from '@/utils/roles'

describe('roles utilities', () => {
	it('checks whether a role is present', () => {
		expect(hasRole([ROLES.ELEVE, ROLES.PROFESSEUR], ROLES.PROFESSEUR)).toBe(true)
		expect(hasRole([ROLES.ELEVE], ROLES.ADMIN)).toBe(false)
	})

	it('checks whether at least one role matches', () => {
		expect(hasAnyRole([ROLES.ELEVE], [ROLES.PROFESSEUR, ROLES.ELEVE])).toBe(true)
		expect(hasAnyRole([ROLES.ELEVE], [ROLES.PROFESSEUR, ROLES.ADMIN])).toBe(false)
	})

	it('checks whether all roles are present', () => {
		expect(hasAllRoles([ROLES.ELEVE, ROLES.PROFESSEUR], [ROLES.ELEVE, ROLES.PROFESSEUR])).toBe(true)
		expect(hasAllRoles([ROLES.ELEVE], [ROLES.ELEVE, ROLES.PROFESSEUR])).toBe(false)
	})

	it('exposes role helpers for each user type', () => {
		expect(isEleve([ROLES.ELEVE])).toBe(true)
		expect(isProfesseur([ROLES.PROFESSEUR])).toBe(true)
		expect(isAdmin([ROLES.ADMIN])).toBe(true)
	})

	it('returns the highest priority role and its label', () => {
		expect(getPrimaryRole([ROLES.ELEVE])).toBe(ROLES.ELEVE)
		expect(getPrimaryRole([ROLES.ELEVE, ROLES.PROFESSEUR])).toBe(ROLES.PROFESSEUR)
		expect(getPrimaryRole([ROLES.ELEVE, ROLES.PROFESSEUR, ROLES.ADMIN])).toBe(ROLES.ADMIN)
		expect(getRoleLabel(ROLES.ELEVE)).toBe('Élève')
		expect(getRoleLabel(ROLES.PROFESSEUR)).toBe('Professeur')
		expect(getRoleLabel(ROLES.ADMIN)).toBe('Administrateur')
		expect(getUserRoleLabel([ROLES.ELEVE, ROLES.PROFESSEUR])).toBe('Professeur')
	})
})