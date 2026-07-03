<template>
  <div v-if="loading" class="state">
    <LoadingModal message="Chargement de vos cours..." size="medium" />
  </div>
  <div v-else class="courses-container">
    <h1>Mes cours</h1>

    <StatusAlert v-model:error="loadError" />

    <template v-if="!loadError">
      <div v-if="courses.length === 0" class="empty-state">
        <p>Vous n'avez commencé aucun cours ou aucun cours ne vous est assigné.</p>
        <button @click="$router.push('/courses')">Découvrir les cours</button>
      </div>
      <div v-else class="courses-list">
        <div v-for="data in courses" :key="data.cours.id" class="course-card">
          <h2 class="course-title">
            {{ data.cours.titre }}
            <BadgeElement :badgeName="data.badge.type"/>
          </h2>
          <div class="course-meta">
            <span class="instructor">{{ data.cours.professeur.prenom }} {{ data.cours.professeur.nom }}</span>
            <TagElement text="En cours" size="small"/>
            <span class="progress">{{ data.pourcentage }}%</span>
          </div>
          <div class="course-footer">
            <button @click="goToCourse(data.cours.id.toString())">Voir le cours</button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import BadgeElement from '@/components/layouts/BadgeElement.vue';
import { useCourseStore } from '@/stores/courseStore';
import type { Course } from '@/types/course';
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import LoadingModal from '@/components/loading/LoadingModal.vue';
import TagElement from '@/components/layouts/TagElement.vue';
import StatusAlert from '@/components/layouts/StatusAlert.vue';

const router = useRouter();
const courseStore = useCourseStore();

const courses = computed<Course[]>(() => courseStore.getMyCourses as Course[]);
const loading = ref(true);
const loadError = ref<unknown>(null);

onMounted(async () => {
  try {
    await courseStore.retrieveMyCourses();
  } catch (error) {
    loadError.value = error;
  } finally {
    loading.value = false;
  }
});

const goToCourse = (courseId: string) => router.push(`/course/${courseId}`);
</script>

<style scoped>
.course-title {
    display: flex;
    align-items: center;
    flex-flow: row nowrap;
    justify-content: space-between;
}

.courses-container {
    padding: 20px;
}

.courses-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.course-card {
  display: flex;
  flex-flow: column;
  justify-content: space-between;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 20px;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s;
}

.course-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}

.description {
  color: var(--text-muted-color);
  font-size: 14px;
  margin: 10px 0;
}

.course-meta {
  display: flex;
  justify-content: space-between;
  margin: 15px 0;
  font-size: 12px;
}

.instructor {
  color: var(--primary-color);
  font-weight: 500;
}

.progress {
  background: var(--secondary-color);
  padding: 2px 8px;
  border-radius: 4px;
}

.course-footer {
  margin-top: 15px;
}

button {
  width: 100%;
  padding: 10px;
  background: var(--primary-color);
  color: var(--white-color);
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

button:hover {
  background: var(--primary-hover-color);
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: var(--text-light-color);
}
</style>
