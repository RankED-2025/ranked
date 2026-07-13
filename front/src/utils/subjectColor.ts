const SUBJECT_COLOR_COUNT = 6

export function getSubjectAccent(matiereId: number): string {
  const index = ((matiereId - 1) % SUBJECT_COLOR_COUNT + SUBJECT_COLOR_COUNT) % SUBJECT_COLOR_COUNT
  return `var(--subject-color-${index + 1})`
}
