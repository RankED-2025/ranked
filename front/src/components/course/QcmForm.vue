<template>
  <div class="qcm-form">
    <h2>{{ qcm.title }}</h2>
    <p>{{ qcm.description }}</p>

    <form @submit.prevent="submitForm">
      <div class="question">
        <p>{{ qcm.question }}</p>

        <div class="options">
          <label v-for="option in qcm.options" :key="option.id" class="option">
            <input
              type="radio"
              :name="qcm.id"
              :value="option.id"
              v-model="selectedAnswer"
            />
            {{ option.text }}
          </label>
        </div>
      </div>

      <button type="submit" class="submit-btn">Submit</button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

defineProps({
  qcm: {
    type: Object,
    required: true,
  }
});

const selectedAnswer = ref<number | null>(null);

function submitForm() {
  console.log('Selected answer ID:', selectedAnswer.value);
  // this.$emit('submit', { qcmId: this.qcm.id, answer: this.selectedAnswer });
  // this.selectedAnswer = null;
}
</script>

<style scoped>
.qcm-form {
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.question {
  margin: 20px 0;
}

.options {
  margin-top: 15px;
}

.option {
  display: block;
  margin: 10px 0;
  cursor: pointer;
}

.submit-btn {
  padding: 10px 20px;
  background-color: #42b983;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.submit-btn:hover {
  background-color: #369970;
}
</style>
