<template>
  <div class="page-view">
    <v-container class="py-8">
      <div class="form-wrap">

        <PageHeader
          icon="mdi-link-variant"
          title="Assigner un cours"
          subtitle="Attribuez un cours à une de vos classes"
        />

        <AssignCourseForm />

      </div>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import AssignCourseForm from '@/components/professor/AssignCourseForm.vue'
import PageHeader from '@/components/layouts/PageHeader.vue'
</script>

<style scoped>
.page-view {
  min-height: calc(100vh - 64px);
  background: var(--background-color);
}

.form-wrap {
  max-width: 560px;
  margin: 0 auto;
}
</style>
