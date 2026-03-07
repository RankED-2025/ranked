<script setup lang="ts">
import { useAuth } from '@/composables'

const { user, isAuthenticated } = useAuth()
</script>

<template>
  <div class="home-view">
    <v-container v-if="isAuthenticated" class="py-8">
      <div class="text-center mb-8">
        <h1 class="text-h3 font-weight-bold mb-4 gradient-text">
          Bienvenue sur Ranked
        </h1>
        <p class="text-h6 text-grey-darken-1">
          Plateforme éducative pour élèves et professeurs
        </p>
      </div>

      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card class="mb-6" elevation="2" rounded="lg">
            <v-card-text class="pa-6">
              <div class="d-flex align-center">
                <v-avatar color="deep-purple" size="64" class="mr-4">
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
              <v-card class="text-center pa-6" elevation="2" rounded="lg" hover>
                <v-icon size="60" color="deep-purple" class="mb-4">mdi-book-open-page-variant</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Cours</h3>
                <p class="text-body-2 text-grey-darken-1">Accédez à vos cours</p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card class="text-center pa-6" elevation="2" rounded="lg" hover>
                <v-icon size="60" color="deep-purple" class="mb-4">mdi-chart-line</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Progression</h3>
                <p class="text-body-2 text-grey-darken-1">Suivez votre progression</p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card class="text-center pa-6" elevation="2" rounded="lg" hover>
                <v-icon size="60" color="deep-purple" class="mb-4">mdi-trophy</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Badges</h3>
                <p class="text-body-2 text-grey-darken-1">Vos récompenses</p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card class="text-center pa-6" elevation="2" rounded="lg" hover>
                <v-icon size="60" color="deep-purple" class="mb-4">mdi-file-document-edit</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">QCM</h3>
                <p class="text-body-2 text-grey-darken-1">Testez vos connaissances</p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card class="text-center pa-6" elevation="2" rounded="lg" hover>
                <v-icon size="60" color="deep-purple" class="mb-4">mdi-account-group</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Classes</h3>
                <p class="text-body-2 text-grey-darken-1">Vos classes</p>
              </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
              <v-card class="text-center pa-6" elevation="2" rounded="lg" hover>
                <v-icon size="60" color="deep-purple" class="mb-4">mdi-cog</v-icon>
                <h3 class="text-h6 font-weight-bold mb-2">Paramètres</h3>
                <p class="text-body-2 text-grey-darken-1">Gérer votre compte</p>
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
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.v-card:hover {
  transform: translateY(-4px);
  transition: transform 0.2s ease-in-out;
}
</style>
