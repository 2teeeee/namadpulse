<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface SymbolListItem {
    id: number
    ticker: string
    name: string
    group: string | null
    last_price: number | null
    change_percent: number | null
}

const { $api } = useNuxtApp()
const route = useRoute()

const page = computed(() => Number(route.query.page) || 1)

const { data, pending } = await useAsyncData(
    () => `symbols-${page.value}`,
    () => $api<{ data: SymbolListItem[]; meta: { current_page: number; last_page: number; total: number } }>(
        '/symbols',
        { query: { page: page.value } }
    ),
    { watch: [page] }
)

const symbols = computed(() => data.value?.data || [])
const meta = computed(() => data.value?.meta)
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">نمادها</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header">فهرست نمادها</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>نام شرکت</th>
                        <th>گروه</th>
                        <th>آخرین قیمت</th>
                        <th>تغییر</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="pending">
                        <td colspan="5" class="text-center text-muted py-4">در حال بارگذاری...</td>
                    </tr>
                    <tr v-else-if="symbols.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">نمادی یافت نشد.</td>
                    </tr>
                    <tr v-for="symbol in symbols" :key="symbol.id">
                        <td>
                            <NuxtLink :to="`/symbols/${symbol.id}`" class="fw-semibold text-decoration-none">
                                {{ symbol.ticker }}
                            </NuxtLink>
                        </td>
                        <td class="text-muted">{{ symbol.name }}</td>
                        <td class="text-muted">{{ symbol.group }}</td>
                        <td class="tabular-nums">{{ symbol.last_price?.toLocaleString('fa-IR') ?? '—' }}</td>
                        <td class="tabular-nums" :class="symbol.change_percent && symbol.change_percent >= 0 ? 'text-positive' : 'text-negative'">
                            <template v-if="symbol.change_percent !== null">
                                <i class="bi" :class="symbol.change_percent >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                                {{ symbol.change_percent }}٪
                            </template>
                            <span v-else class="text-muted">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <nav v-if="meta && meta.last_page > 1" class="mt-3">
        <ul class="pagination">
            <li v-for="p in meta.last_page" :key="p" class="page-item" :class="{ active: p === meta.current_page }">
                <NuxtLink class="page-link" :to="{ query: { page: p } }">{{ p }}</NuxtLink>
            </li>
        </ul>
    </nav>
</template>
