/**
 * Utilitaires de manipulation de chaînes de caractères
 */

/**
 * Returns the string with the first char as an uppercase letter.
 * @param string
 */
export const ucFirst = (string: string): string => {
  switch (string.length) {
    case 0:
      return ''
    case 1:
      return string.toUpperCase()
    default:
      return string.charAt(0).toUpperCase() + string.slice(1)
  }
}
