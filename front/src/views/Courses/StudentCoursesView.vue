<template>
  <div v-if="loading" class="state">
    <LoadingModal message="Chargement de vos cours..." size="medium" />
  </div>
  <v-alert v-else-if="courseStore.getError" type="error" rounded="lg" class="ma-6">
    {{ courseStore.getError }}
  </v-alert>
  <v-container v-else class="py-8">
    <h1 class="text-h4 font-weight-bold mb-6">Mes cours</h1>

    <div v-if="courses.length === 0" class="text-center py-12">
      <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-book-open-outline</v-icon>
      <p class="text-h6 text-grey-darken-1 mb-6">
        Vous n'avez commencé aucun cours ou aucun cours ne vous est assigné.
      </p>
    </div>

    <v-row v-else>
      <v-col v-for="data in courses" :key="data.cours.id" cols="12" sm="6" lg="4">
        <v-card elevation="2" rounded="lg" class="d-flex flex-column h-100" hover>
          <v-card-title class="d-flex justify-space-between align-center pt-4 pb-1">
            <span class="text-body-1 font-weight-bold text-wrap">{{ data.cours.titre }}</span>
            <BadgeElement :badgeName="data.badge.type" />
          </v-card-title>

          <v-card-text class="flex-grow-1">
            <div class="text-body-2 text-primary font-weight-medium mb-4">
              {{ data.cours.professeur.prenom }} {{ data.cours.professeur.nom }}
            </div>
            <div class="d-flex align-center justify-space-between mb-2">
              <span class="text-caption text-grey-darken-1">Progression</span>
              <span class="text-caption font-weight-bold">{{ data.pourcentage }}%</span>
            </div>
            <v-progress-linear
              :model-value="data.pourcentage"
              color="primary"
              bg-color="grey-lighten-3"
              rounded
              height="8"
            />
          </v-card-text>

          <v-card-actions class="pa-4 pt-0">
            <v-btn
              color="primary"
              variant="elevated"
              block
              @click="goToCourse(data.cours.id.toString())"
            >
              Voir le cours
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import BadgeElement from '@/components/layouts/BadgeElement.vue';
import { useCourseStore } from '@/stores/courseStore';
import type { Course } from '@/types/course';
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import LoadingModal from '@/components/loading/LoadingModal.vue';

const router = useRouter();
const courseStore = useCourseStore();

const courses = computed<Course[]>(() => courseStore.getMyCourses as Course[]);
const loading = ref(true);

onMounted(async () => {
  await courseStore.retrieveMyCourses();
  loading.value = false;
});

const goToCourse = (courseId: string) => router.push(`/course/${courseId}`);
</script>

<style scoped>
.state {
  color: var(--text-muted-color);
}
</style>
