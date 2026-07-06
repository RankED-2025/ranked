<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import type { Classe } from '@/types'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

const router = useRouter()
const classes = ref<Classe[]>([])
const loading = ref(false)
const error = ref<unknown>(null)

onMounted(async () => {
  loading.value = true
  try {
    classes.value = await courseService.getProfessorClasses()
  } catch (err) {
    error.value = err
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="classes-view">
    <v-container class="py-8">
      <div class="d-flex align-center mb-6">
        <v-btn icon variant="text" @click="router.back()" class="mr-2">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <h1 class="text-h4 font-weight-bold gradient-text">Mes Classes</h1>
      </div>

      <v-progress-circular v-if="loading" indeterminate color="primary" class="d-block mx-auto" />

      <StatusAlert v-else-if="error" v-model:error="error" />

      <v-row v-else-if="classes.length > 0">
        <v-col v-for="classe in classes" :key="classe.id" cols="12" sm="6" md="4">
          <v-card
            elevation="2"
            rounded="lg"
            hover
            style="cursor: pointer"
            @click="router.push(`/professor/classes/${classe.id}`)"
          >
            <v-card-text class="pa-6 text-center">
              <v-icon size="56" color="primary" class="mb-3">mdi-account-group</v-icon>
              <div class="text-h6 font-weight-bold">{{ classe.nom }}</div>
              <div class="text-caption text-grey mt-1">Voir les cours assignés</div>
            </v-card-text>
            <v-card-actions class="justify-center pb-4">
              <v-btn color="primary" variant="tonal" size="small" rounded>
                Voir les cours
                <v-icon end>mdi-arrow-right</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>

      <v-card v-else elevation="1" rounded="lg" class="text-center pa-8">
        <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group-outline</v-icon>
        <div class="text-h6 text-grey-darken-1 mb-2">Aucune classe pour le moment</div>
        <div class="text-body-2 text-grey">Vos classes apparaîtront ici une fois créées.</div>
      </v-card>
    </v-container>
  </div>
</template>

<style scoped>
.classes-view {
  min-height: calc(100vh - 64px);
  background: var(--gradient-background);
}

.gradient-text {
  background: var(--gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.v-card:hover {
  transform: translateY(-4px);
  transition: transform 0.2s ease-in-out;
}
</style>
