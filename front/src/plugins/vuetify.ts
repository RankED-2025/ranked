import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        dark: false,
        colors: {
          primary: '#2E3C88',
          secondary: '#CDD1D3',
          error: '#B02E0C',
          danger: '#B02E0C',
          warning: '#FF8600',
          success: '#0C7C59',
          background: '#EEF0F2',
          surface: '#EEF0F2',
          'on-primary': '#EEF0F2',
          'on-secondary': '#040F16',
          'on-error': '#EEF0F2',
          'on-warning': '#040F16',
          'on-success': '#EEF0F2',
          'on-background': '#040F16',
          'on-surface': '#040F16',
        },
      },
    },
  },
  defaults: {
    global: {
      ripple: false,
    },
    VCard: { rounded: 'lg' },
    VBtn: { rounded: 'lg' },
    VTextField: { rounded: 'lg' },
    VSelect: { rounded: 'lg' },
    VTextarea: { rounded: 'lg' },
    VChip: { rounded: 'lg' },
    VAlert: { rounded: 'lg' },
    VDialog: { rounded: 'lg' },
  },
})
