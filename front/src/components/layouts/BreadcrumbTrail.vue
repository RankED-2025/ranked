<script setup lang="ts">
import { computed } from 'vue'
import router from '@/router'
import { RouterLink, useRoute } from 'vue-router'
import type { BreadcrumbMeta, BreadcrumbItem, BreadcrumbFromRouter } from '@/types/component/layouts/breadcrumb'

const route = useRoute()

const formatFallbackLabel = (path: string): string => {
	const segments = path.split('/').filter(Boolean)

	if (segments.length === 0) {
		return 'Accueil'
	}

	return segments
		.map((segment) => segment.replace(/[-_]/g, ' '))
		.map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
		.join(' / ')
}

const getBreadcrumbMeta = (routeName: string): BreadcrumbMeta | undefined => {
	const routeRecord = router.getRoutes().find((record) => record.name === routeName)
	return routeRecord?.meta.breadcrumb as BreadcrumbMeta | undefined
}

const buildBreadcrumbTrail = (routeName: string): BreadcrumbItem[] => {
	const routeChain: BreadcrumbFromRouter[] = []
	let currentRouteName: string | undefined = routeName
	let guard = 0

	while (currentRouteName && guard < 20) {
		const breadcrumbMeta = getBreadcrumbMeta(currentRouteName)

		if (!breadcrumbMeta) {
			break
		}

		routeChain.unshift({
			name: currentRouteName,
			label: breadcrumbMeta.label,
		})
		currentRouteName = breadcrumbMeta.parentName
		guard += 1
	}

	if (routeChain.length === 0) {
		return [{ label: formatFallbackLabel(route.path) }]
	}

	const resolveBreadcrumbPath = (name: string): string => {
		const resolvedRoute = router.resolve({ name })
		return resolvedRoute.path
	}

	return routeChain.map((item, index) => ({
		label: item.label,
		to: index < routeChain.length - 1 ? resolveBreadcrumbPath(item.name) : undefined,
	}))
}

const breadcrumbs = computed(() => {
	const routeName = typeof route.name === 'string' ? route.name : ''
	return buildBreadcrumbTrail(routeName)
})
</script>

<template>
	<nav class="breadcrumb-trail" aria-label="Fil d'Ariane">
		<ol class="breadcrumb-list">
			<li
				v-for="(item, index) in breadcrumbs"
				:key="`${item.label}-${index}`"
				class="breadcrumb-item"
			>
				<RouterLink
					v-if="item.to && index < breadcrumbs.length - 1"
					:to="item.to"
					class="breadcrumb-link"
				>
					{{ item.label }}
				</RouterLink>
				<span v-else class="breadcrumb-current">{{ item.label }}</span>
				<span v-if="index < breadcrumbs.length - 1" class="breadcrumb-separator">></span>
			</li>
		</ol>
	</nav>
</template>

<style scoped>
.breadcrumb-trail {
	padding: 1rem 2rem 0;
	font-size: 0.8rem;
	line-height: 1.2;
	color: var(--text-secondary-color, rgba(0, 0, 0, 0.6));
	user-select: none;
}

.breadcrumb-list {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 0.35rem;
	margin: 0;
	padding: 0;
	list-style: none;
}

.breadcrumb-item {
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
}

.breadcrumb-link {
	color: inherit;
	text-decoration: none;
	opacity: 0.8;
	transition: opacity 0.2s ease;
}

.breadcrumb-link:hover {
	opacity: 1;
	text-decoration: underline;
	text-underline-offset: 0.16em;
}

.breadcrumb-current {
	font-weight: 600;
    text-decoration: underline;
	color: var(--black-color);
}

.breadcrumb-separator {
	opacity: 0.35;
}
</style>
