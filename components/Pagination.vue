<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        currentPage: number
        lastPage: number
        siblingCount?: number
    }>(),
    { siblingCount: 1 }
)

const emit = defineEmits<{ change: [page: number] }>()

function range(start: number, end: number): number[] {
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
}

// خروجی چیزی مثل [1, '...', 5, 6, 7, '...', 42] — هیچ‌وقت همه‌ی شماره‌ها یک‌جا نمایش داده نمی‌شن
const pages = computed<(number | '...')[]>(() => {
    const total = props.lastPage
    const current = props.currentPage
    const totalSlots = props.siblingCount * 2 + 5

    if (total <= totalSlots) {
        return range(1, total)
    }

    const leftSibling = Math.max(current - props.siblingCount, 1)
    const rightSibling = Math.min(current + props.siblingCount, total)

    const showLeftEllipsis = leftSibling > 2
    const showRightEllipsis = rightSibling < total - 1

    if (!showLeftEllipsis && showRightEllipsis) {
        return [...range(1, 3 + props.siblingCount * 2), '...', total]
    }

    if (showLeftEllipsis && !showRightEllipsis) {
        return [1, '...', ...range(total - (3 + props.siblingCount * 2) + 1, total)]
    }

    return [1, '...', ...range(leftSibling, rightSibling), '...', total]
})

function go(page: number | '...') {
    if (page === '...' || page === props.currentPage) return
    emit('change', page)
}
</script>

<template>
    <nav v-if="lastPage > 1" aria-label="صفحه‌بندی">
        <ul class="pagination mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button type="button" class="page-link" :disabled="currentPage === 1" @click="go(currentPage - 1)">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </li>

            <li
                v-for="(page, index) in pages"
                :key="index"
                class="page-item"
                :class="{ active: page === currentPage, disabled: page === '...' }"
            >
                <span v-if="page === '...'" class="page-link">…</span>
                <button v-else type="button" class="page-link" @click="go(page)">{{ page }}</button>
            </li>

            <li class="page-item" :class="{ disabled: currentPage === lastPage }">
                <button type="button" class="page-link" :disabled="currentPage === lastPage" @click="go(currentPage + 1)">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </li>
        </ul>
    </nav>
</template>
