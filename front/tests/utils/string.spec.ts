import { describe, expect, it } from 'vitest'
import { ucFirst } from '@utils/string'

describe('ucFirst', () => {
	it('returns an empty string for an empty input', () => {
		expect(ucFirst('')).toBe('')
	})

	it('uppercases a single character', () => {
		expect(ucFirst('a')).toBe('A')
	})

	it('uppercases only the first character of a longer string', () => {
		expect(ucFirst('bonjour')).toBe('Bonjour')
	})

	it('keeps the rest of the string unchanged', () => {
		expect(ucFirst('école')).toBe('École')
	})
})