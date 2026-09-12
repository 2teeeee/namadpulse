<script setup lang="ts">
defineProps<{
    label: string
    value: string | number
    deltaPercent?: number | null
    isPositive?: boolean | null
}>()
</script>

<template>
    <div class="stat-card">
        <div class="stat-card__label">{{ label }}</div>
        <div class="stat-card__value tabular-nums">{{ value }}</div>
        <div
            v-if="deltaPercent !== undefined && deltaPercent !== null"
            class="stat-card__delta"
            :class="isPositive ? 'is-positive' : 'is-negative'"
        >
            <i class="bi" :class="isPositive ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
            {{ deltaPercent }}٪
        </div>
    </div>
</template>
