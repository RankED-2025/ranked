export type BreadcrumbFromRouter = {
	name: string
	label: string
}

export type BreadcrumbItem = {
	label: string
	to?: string
}

export type BreadcrumbMeta = {
	label: string
	parentName?: string
}