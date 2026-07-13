<template>
  <div class="page-view">
    <v-container class="py-8">
      <div class="form-wrap">

        <PageHeader
          icon="mdi-pencil-outline"
          title="Modifier le cours"
          subtitle="Modifiez les informations et les activités de ce cours"
        />

        <CourseForm mode="edit" />

      </div>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import CourseForm from '@/components/professor/CourseForm.vue'
import PageHeader from '@/components/layouts/PageHeader.vue'
</script>

<style scoped>
.page-view {
  min-height: calc(100vh - 64px);
  background: var(--background-color);
}

.form-wrap {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
