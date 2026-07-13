import { DEFAULT_STATUS_MESSAGES } from '../../src/utils'
import type { MessageSeverity } from '../../src/types'

export type StatusMessageCase = { status: number; message: string; type: MessageSeverity }

/**
 * Builds the it.each/describe.each dataset for every DEFAULT_STATUS_MESSAGES entry.
 * Pass the statuses a page overrides so they can be excluded and tested separately.
 */
export const defaultStatusMessageCases = (excludedStatuses: number[] = []): StatusMessageCase[] =>
  Object.entries(DEFAULT_STATUS_MESSAGES)
    .filter(([status]) => !excludedStatuses.includes(Number(status)))
    .map(([status, { message, type }]) => ({ status: Number(status), message, type }))
