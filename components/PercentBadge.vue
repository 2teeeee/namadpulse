<script setup lang="ts">
defineProps<{
    percent?: number | null
    isAbove?: boolean | null
}>()
</script>

<template>
    <span v-if="percent !== null && percent !== undefined" :class="isAbove ? 'text-positive' : 'text-negative'" style="font-size: 0.78rem;">
        {{ percent }}٪
    </span>
    <span v-else class="text-muted">—</span>
</template>
