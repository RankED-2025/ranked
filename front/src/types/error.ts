export type MessageSeverity = 'error' | 'warning' | 'info' | 'success'

export interface StatusMessage {
  type: MessageSeverity
  message: string
}

export interface StatusMessageOverride {
  status: number
  type: MessageSeverity
  message: string
}
