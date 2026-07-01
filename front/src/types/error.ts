export type MessageSeverity = 'error' | 'warning' | 'info' | 'success'

export interface ApiError {
  response?: {
    status?: number
    data?: {
      error?: string
      message?: string
      code?: number
    }
  }
  message?: string
}

export interface StatusMessage {
  type: MessageSeverity
  message: string
}

export interface StatusMessageOverride {
  status: number
  type: MessageSeverity
  message: string
}
