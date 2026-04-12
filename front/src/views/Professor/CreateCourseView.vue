<template>
  <div class="professor-view">
    <v-container class="py-8 d-flex align-center justify-center" style="min-height: 100vh;">
      <CreateCourseForm />
    </v-container>
  </div>
</template>

<script setup lang="ts">
import CreateCourseForm from '@/components/professor/CreateCourseForm.vue'
</script>

<style scoped>
.professor-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
