import type { PaletteColor, PaletteColorsCollection } from '@/types'

/**
 * Unified chart colour palette mixing project brand colours and Chart.js defaults.
 * Use getRotatingColors(n) for all charts except badge charts.
 * Use getBadgeColors(types) for badge charts.
 */

const PALETTE: PaletteColor[] = [
  // — project colours —
  { bg: 'rgba(46,  60,  136, 0.75)', border: 'rgba(46,  60,  136, 1)' }, // primary
  { bg: 'rgba(12,  124,  89, 0.75)', border: 'rgba(12,  124,  89, 1)' }, // success
  { bg: 'rgba(255, 134,   0, 0.75)', border: 'rgba(255, 134,   0, 1)' }, // warning
  { bg: 'rgba(176,  46,  12, 0.75)', border: 'rgba(176,  46,  12, 1)' }, // danger
  // — Chart.js defaults —
  { bg: 'rgba(75,  192, 192, 0.75)', border: 'rgba(75,  192, 192, 1)' }, // teal
  { bg: 'rgba(54,  162, 235, 0.75)', border: 'rgba(54,  162, 235, 1)' }, // blue
  { bg: 'rgba(153, 102, 255, 0.75)', border: 'rgba(153, 102, 255, 1)' }, // purple
  { bg: 'rgba(255, 205,  86, 0.75)', border: 'rgba(255, 205,  86, 1)' }, // yellow
  { bg: 'rgba(255,  99, 132, 0.75)', border: 'rgba(255,  99, 132, 1)' }, // red
  { bg: 'rgba(255, 159,  64, 0.75)', border: 'rgba(255, 159,  64, 1)' }, // orange
  { bg: 'rgba(201, 203, 207, 0.75)', border: 'rgba(201, 203, 207, 1)' }, // grey
]

export function getRotatingColors(count: number): PaletteColorsCollection {
  return {
    bg: Array.from({ length: count }, (_, i) => PALETTE[i % PALETTE.length]!.bg),
    border: Array.from({ length: count }, (_, i) => PALETTE[i % PALETTE.length]!.border),
  }
}

// Badge-specific colours — bg + border matched to badge value
const BADGE_MAP: Record<string, PaletteColor> = {
  none:     { bg: 'rgba(201, 203, 207, 0.75)', border: 'rgba(201, 203, 207, 1)' },
  bronze:   { bg: 'rgba(176, 104,  32, 0.75)', border: 'rgba(176, 104,  32, 1)' },
  silver:   { bg: 'rgba(166, 166, 166, 0.75)', border: 'rgba(166, 166, 166, 1)' },
  argent:   { bg: 'rgba(166, 166, 166, 0.75)', border: 'rgba(166, 166, 166, 1)' },
  gold:     { bg: 'rgba(218, 165,  32, 0.75)', border: 'rgba(218, 165,  32, 1)' },
  or:       { bg: 'rgba(218, 165,  32, 0.75)', border: 'rgba(218, 165,  32, 1)' },
  platinum: { bg: 'rgba(100, 180, 210, 0.75)', border: 'rgba(100, 180, 210, 1)' },
  platine:  { bg: 'rgba(100, 180, 210, 0.75)', border: 'rgba(100, 180, 210, 1)' },
}

const BADGE_FALLBACK: PaletteColor = {
  bg:     'rgba(46, 60, 136, 0.75)',
  border: 'rgba(46, 60, 136, 1)',
}

export function getBadgeColors(types: string[]): PaletteColorsCollection {
  return {
    bg:     types.map((t) => (BADGE_MAP[t.toLowerCase()] ?? BADGE_FALLBACK).bg),
    border: types.map((t) => (BADGE_MAP[t.toLowerCase()] ?? BADGE_FALLBACK).border),
  }
}
