<template>
    <div class="badge-element">
        <img
          :src="badgeImagePath ?? defaultBadge"
          :alt="badgeName"
          class="badge-image"
          width="50"
          height="50"
        />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { default as defaultBadge, bronze, silver, gold, platinum, diamond } from '@assets/img/badges/index';

const props = defineProps<{
  badgeName: string
}>()

const badgeImagePath = computed(() => {
  switch (props.badgeName) {
    case 'bronze':
      return bronze;
    case 'fer':
      return silver;
    case 'or':
      return gold;
    case 'platine':
      return platinum;
    case 'diamant':
      return diamond;
    default:
      return defaultBadge;
  }
});
</script>

<style scoped>
.badge-element {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.badge-image {
    object-fit: contain;
}
</style>
