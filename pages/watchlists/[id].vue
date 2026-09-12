<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface SymbolItem {
    id: number
    ticker: string
    name: string
    group: string | null
    last_price: number | null
    change_percent: number | null
}

const route = useRoute()
const { $api } = useNuxtApp()

const { data, refresh } = await useAsyncData(`watchlist-${route.params.id}`, () =>
    $api<{ data: { id: number; name: string; symbols: SymbolItem[] } }>(`/watchlists/${route.params.id}`)
)

const watchlist = computed(() => data.value?.data)

async function removeSymbol(symbolId: number) {
    await $api(`/watchlists/${route.params.id}/symbols/${symbolId}`, { method: 'DELETE' })
    await refresh()
}
</script>

<template>
    <template v-if="watchlist">
        <div class="page-header">
            <div>
                <h1 class="page-header__title">{{ watchlist.name }}</h1>
            </div>
        </div>

        <div class="card">
            <div class="card-header">نمادهای این واچ‌لیست</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>نماد</th>
                            <th>گروه</th>
                            <th>آخرین قیمت</th>
                            <th>تغییر</th>
                            <th class="text-end">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="watchlist.symbols.length === 0">
                            <td colspan="5" class="text-center text-muted py-4">
                                هنوز نمادی به این واچ‌لیست اضافه نشده — از
                                <NuxtLink to="/symbols">صفحه نمادها</NuxtLink>
                                یا صفحه‌ی فیلتر می‌تونید اضافه کنید.
                            </td>
                        </tr>
                        <tr v-for="symbol in watchlist.symbols" :key="symbol.id">
                            <td>
                                <NuxtLink :to="`/symbols/${symbol.id}`" class="fw-semibold text-decoration-none">
                                    {{ symbol.ticker }}
                                </NuxtLink>
                            </td>
                            <td class="text-muted">{{ symbol.group }}</td>
                            <td class="tabular-nums">{{ symbol.last_price?.toLocaleString('fa-IR') ?? '—' }}</td>
                            <td class="tabular-nums" :class="symbol.change_percent && symbol.change_percent >= 0 ? 'text-positive' : 'text-negative'">
                                <template v-if="symbol.change_percent !== null">{{ symbol.change_percent }}٪</template>
                                <span v-else class="text-muted">—</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-danger" @click="removeSymbol(symbol.id)">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </template>
</template>
