import { beforeEach, vi } from 'vitest'

// jsdom may receive a `--localstorage-file` arg without a valid path, which
// produces a broken Storage object missing `clear()`. Replace it with a
// fully-functional in-memory implementation.

const createStorageMock = () => {
  const store: Record<string, string> = {}
  return {
    getItem: (key: string): string | null => store[key] ?? null,
    setItem: (key: string, value: string): void => { store[key] = String(value) },
    removeItem: (key: string): void => { delete store[key] },
    clear: (): void => { Object.keys(store).forEach((k) => delete store[k]) },
    get length(): number { return Object.keys(store).length },
    key: (i: number): string | null => Object.keys(store)[i] ?? null,
  }
}

vi.stubGlobal('localStorage', createStorageMock())

beforeEach(() => {
  localStorage.clear()
})
