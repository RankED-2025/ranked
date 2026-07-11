<template>
  <div class="page-view">
    <v-container class="py-8">
      <div class="form-wrap">

        <v-card elevation="1" rounded="lg" class="page-header-card mb-4">
          <v-card-text class="py-3 px-4">
            <div class="d-flex align-center ga-3">
              <v-avatar size="34" color="primary" variant="tonal" rounded="lg">
                <v-icon size="16">mdi-plus-circle-outline</v-icon>
              </v-avatar>
              <div>
                <p class="text-body-2 font-weight-bold mb-0">Créer un cours</p>
                <p class="text-caption text-medium-emphasis mb-0">Concevez un nouveau cours pour vos élèves</p>
              </div>
            </div>
          </v-card-text>
        </v-card>

        <CreateCourseForm />

      </div>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import CreateCourseForm from '@/components/professor/CreateCourseForm.vue'
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

.page-header-card {
  border-left: 3px solid var(--primary-color) !important;
}
</style>
