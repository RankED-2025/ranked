<script setup lang="ts">
import { computed } from 'vue'
import { useAuth } from '@/composables'
import { useRouter } from 'vue-router'
import { isProfesseur } from '@/utils'

const { user, isAuthenticated } = useAuth()
const router = useRouter()

const isProfessor = computed(() => isProfesseur(user.value?.roles ?? []))

const redirectTo = (path: string) => router.push(path)

const getProfessorCardListeners = (path: string) => {
  if (!isProfessor.value) {
    return {}
  }

  return {
    click: () => redirectTo(path),
  }
}
</script>

<template>
  <div class="home-view">
    <v-container v-if="isAuthenticated" class="py-8">
      <div class="text-center mb-8">
        <h1 class="text-h3 font-weight-bold mb-4 gradient-text">Bienvenue sur Ranked</h1>
        <p class="text-h6 text-grey-darken-1">Plateforme éducative pour élèves et professeurs</p>
      </div>

      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card class="mb-6" elevation="2" rounded="lg">
            <v-card-text class="pa-6">
              <div class="d-flex align-center">
                <v-avatar color="primary" size="64" class="mr-4">
                  <v-icon size="40" color="white">mdi-account-circle</v-icon>
                </v-avatar>
                <div>
                  <div class="text-overline text-grey-darken-1">Connecté en tant que</div>
                  <div class="text-h5 font-weight-bold">{{ user?.email }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <v-row>
            <v-col cols="12" sm="6" md="4">
              <v-card
                class="text-center pa-6"
                elevation="2"
                rounded="lg"
                hover
                style="cursor: pointer;"
                @click="isProfessor ? redirectTo('/professor/my-courses') : redirectTo('/my-courses')"
              >
                <v-icon size="60" color="primary" class="mb-4">mdi-book-open-page-variant</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Cours</h3>
                <p class="text-body-2 text-grey-darken-1">
                  {{ isProfessor ? 'Vos cours créés' : 'Accédez à vos cours' }}
                </p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card
                class="text-center pa-6"
                :class="{ 'clickable-card': isProfessor }"
                elevation="2"
                rounded="lg"
                :hover="isProfessor"
                v-on="getProfessorCardListeners('/professor/classes')"
              >
                <v-icon size="60" color="primary" class="mb-4">mdi-chart-line</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Progression</h3>
                <p class="text-body-2 text-grey-darken-1">
                  {{ isProfessor ? 'Progression de vos élèves' : 'Suivez votre progression' }}
                </p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card
                class="text-center pa-6"
                :class="{ 'clickable-card': !isProfessor }"
                elevation="2"
                rounded="lg"
                :hover="!isProfessor"
                v-on="!isProfessor ? { click: () => redirectTo('/my-badges-competences') } : {}"
              >
                <v-icon size="60" color="primary" class="mb-4">mdi-trophy</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Badges &amp; Compétences</h3>
                <p class="text-body-2 text-grey-darken-1">Vos récompenses et acquis</p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card
                class="text-center pa-6"
                elevation="2"
                rounded="lg"
                hover
                @click="redirectTo('/stats')"
              >
                <v-icon size="60" color="primary" class="mb-4">mdi-file-document-edit</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Statistiques</h3>
                <p class="text-body-2 text-grey-darken-1">
                  Consultez des statistiques globales et personelles
                </p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card
                class="text-center pa-6"
                :class="{ 'clickable-card': isProfessor }"
                elevation="2"
                rounded="lg"
                :hover="isProfessor"
                v-on="getProfessorCardListeners('/professor/classes')"
              >
                <v-icon size="60" color="primary" class="mb-4">mdi-account-group</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Classes</h3>
                <p class="text-body-2 text-grey-darken-1">
                  {{ isProfessor ? 'Cours assignés & progressions' : 'Vos classes' }}
                </p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card class="text-center pa-6" elevation="2" rounded="lg" hover>
                <v-icon size="60" color="primary" class="mb-4">mdi-cog</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Paramètres</h3>
                <p class="text-body-2 text-grey-darken-1">Gérer votre compte</p>
              </v-card>
            </v-col>

            <v-col v-if="isProfessor" cols="12" sm="6" md="4">
              <v-card
                class="text-center pa-6"
                elevation="2"
                rounded="lg"
                hover
                style="cursor: pointer"
                @click="redirectTo('/professor/create-course')"
              >
                <v-icon size="60" color="success" class="mb-4">mdi-plus-circle</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Créer un cours</h3>
                <p class="text-body-2 text-grey-darken-1">Créer un nouveau cours</p>
              </v-card>
            </v-col>

            <v-col v-if="isProfessor" cols="12" sm="6" md="4">
              <v-card
                class="text-center pa-6"
                elevation="2"
                rounded="lg"
                hover
                style="cursor: pointer"
                @click="redirectTo('/professor/assign-course')"
              >
                <v-icon size="60" color="info" class="mb-4">mdi-link-variant</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Assigner un cours</h3>
                <p class="text-body-2 text-grey-darken-1">Assigner à une classe</p>
              </v-card>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<style scoped>
.home-view {
  min-height: calc(100vh - 64px);
  background: var(--gradient-background);
}

.gradient-text {
  background: var(--gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.clickable-card {
  cursor: pointer;
}

.clickable-card:hover {
  transform: translateY(-4px);
  transition: transform 0.2s ease-in-out;
}
</style>
