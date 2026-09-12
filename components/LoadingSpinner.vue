<script setup lang="ts">
withDefaults(defineProps<{ label?: string; size?: 'sm' | 'md' }>(), {
    label: 'در حال بارگذاری...',
    size: 'md',
})
</script>

<template>
    <div class="loading-block" :class="size === 'sm' ? 'py-3' : 'py-5'">
        <div class="loading-spinner" :class="size === 'sm' ? 'loading-spinner--sm' : ''"></div>
        <span class="text-muted" style="font-size: 0.85rem;">{{ label }}</span>
    </div>
</template>
