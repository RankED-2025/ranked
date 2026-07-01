export function getApiErrorMessage(error: unknown, fallback: string): string {
  if (error && typeof error === 'object') {
    const apiErr = error as { response?: { data?: { error?: string; message?: string } }; message?: string }
    return apiErr.response?.data?.error || apiErr.response?.data?.message || apiErr.message || fallback
  }
  return fallback
}
