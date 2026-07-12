<template>
  <div class="page-view">
    <v-container class="py-8">
      <div class="form-wrap">

        <PageHeader
          icon="mdi-plus-circle-outline"
          title="Créer un cours"
          subtitle="Concevez un nouveau cours pour vos élèves"
        />

        <CreateCourseForm />

      </div>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import CreateCourseForm from '@/components/professor/CreateCourseForm.vue'
import PageHeader from '@/components/layouts/PageHeader.vue'
</script>

<style scoped>
.page-view {
  min-height: calc(100vh - 64px);
  background: var(--background-color);
}

.form-wrap {
  max-width: 1100px;
  margin: 0 auto;
}
</style>
