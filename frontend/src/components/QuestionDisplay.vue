<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="badge bg-secondary fs-6">
            Pregunta {{ questionIndex + 1 }} / {{ totalQuestions }}
        </span>
        
        </div>

    <div class="card p-4 mb-4 border-info">
        <h5 class="mb-4">
            {{ question.description }}
        </h5>
        
        <small class="mb-4">
            <strong>Debes seleccionar solo una opción para esta pregunta:</strong>
        </small>
        
        <div class="list-group">
            <button 
                v-for="option in question.options" 
                :key="option.id" 
                @click="emit('select', question.id, option.id)"
                :class="['list-group-item', 'list-group-item-action', 'my-2', 
                         { 'active bg-primary text-white': selectedOptionId === option.id }]"
                type="button"
            >
                {{ option.text }}
            </button>
        </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    question: Object,
    questionIndex: Number,
    totalQuestions: Number,
    selectedOptionId: [Number, String],
});

const emit = defineEmits(['select']);
</script>

<style scoped>
.active.bg-primary {
    border-color: #0d6efd !important;
}
</style>