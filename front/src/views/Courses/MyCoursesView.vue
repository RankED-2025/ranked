<template>
  <div v-if="loading" class="state">
    <LoadingModal message="Chargement de vos cours..." size="medium" />
  </div>
  <div v-else class="courses-container">
    <h1>My Courses</h1>

        <div v-if="courses.length === 0" class="empty-state">
            <p>Vous n'avez commencé aucun cours ou aucun cours ne vous est assigné.</p>
            <button @click="$router.push('/courses')">Découvrir les cours</button>
        </div>

        <div v-else class="courses-list">
            <div v-for="data in courses" :key="data.cours.id" class="course-card">
                <h2 class="course-title">{{ data.cours.title }} <BadgeElement :badgeName="data.badge.type"/></h2>
                <div class="course-meta">
                    <span class="instructor">{{ data.cours.professeur.firstName }} {{ data.cours.professeur.name }}</span>
                    <span class="progress">{{ data.percentage }}%</span>
                </div>
                <div class="course-footer">
                    <button @click="goToCourse(data.cours.id.toString())">Voir le cours</button>
                </div>
            </div>
        </div>
    </div>
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
const courses = computed<Course[]>(() => {
    console.log('Courses from store:', courseStore.getMyCourses);
    return courseStore.getMyCourses as Course[]
});
const loading = ref(true);

onMounted(async () => {
    try {
        await courseStore.retrieveMyCourses();
        loading.value = false;
    } catch (error) {
        console.error('Err:', error);
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
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.description {
    color: #666;
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
    color: #0066cc;
    font-weight: 500;
}

.progress {
    background: #e0e0e0;
    padding: 2px 8px;
    border-radius: 4px;
}

.course-footer {
    margin-top: 15px;
}

button {
    width: 100%;
    padding: 10px;
    background: #0066cc;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

button:hover {
    background: #0052a3;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}
</style>
