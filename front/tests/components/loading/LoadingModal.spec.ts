import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import LoadingModal from '../../../src/components/loading/LoadingModal.vue'

describe('LoadingModal.vue', () => {
	it('renders the default loading message and medium size by default', () => {
		const wrapper = mount(LoadingModal)

		expect(wrapper.find('.loading-modal').exists()).toBe(true)
		expect(wrapper.find('.loading-backdrop').exists()).toBe(true)
		expect(wrapper.find('.loading-modal-panel').classes()).toContain('size-medium')
		expect(wrapper.find('.loading-message').text()).toBe(
			'Chargement en cours, merci de patienter...',
		)
		expect(wrapper.find('.spinner-medium').exists()).toBe(true)
	})

	it('renders the provided message and forwards the size to the panel and loader', () => {
		const wrapper = mount(LoadingModal, {
			props: {
				message: 'Veuillez patienter...',
				size: 'large',
			},
		})

		expect(wrapper.find('.loading-modal-panel').classes()).toContain('size-large')
		expect(wrapper.find('.loading-message').text()).toBe('Veuillez patienter...')
		expect(wrapper.find('.spinner-large').exists()).toBe(true)
	})
})
