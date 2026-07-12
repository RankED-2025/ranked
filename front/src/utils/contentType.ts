const CONTENT_TYPE_META: Record<string, { icon: string; label: string }> = {
  video: { icon: 'mdi-play-box-outline', label: 'Vidéo' },
  article: { icon: 'mdi-file-document-outline', label: 'Article' },
  pdf: { icon: 'mdi-file-pdf-box', label: 'PDF' },
  image: { icon: 'mdi-image-outline', label: 'Image' },
}

export function contentTypeMeta(type: string | undefined) {
  return CONTENT_TYPE_META[type ?? ''] ?? { icon: 'mdi-file-outline', label: 'Contenu' }
}
