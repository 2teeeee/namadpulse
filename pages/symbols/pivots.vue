<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface LevelInfo { value: number; percent: number | null; is_above: boolean }
interface SymbolPivotRow { id: number; ticker: string; name: string; levels: Record<string, LevelInfo> | null }
interface SymbolGroupOption { id: number; name: string }

const { $api } = useNuxtApp()
const route = useRoute()
const router = useRouter()

const period = computed(() => (route.query.period as string) || 'year')
const q = computed(() => (route.query.q as string) || '')
const symbolGroupId = computed(() => (route.query.symbol_group_id as string) || '')
const page = computed(() => Number(route.query.page) || 1)

const periodOptions: Record<string, string> = { year: 'سالانه', quarter: 'فصلی', month: 'ماهانه' }
const levelLabels: Record<string, string> = { pp: 'PP', r1: 'R1', r2: 'R2', r3: 'R3', s1: 'S1', s2: 'S2', s3: 'S3' }

const { data: groupsData } = await useAsyncData('symbol-groups', () =>
    $api<{ data: SymbolGroupOption[] }>('/symbol-groups')
)
const symbolGroups = computed(() => groupsData.value?.data || [])

const { data, pending } = await useAsyncData(
    () => `pivots-${period.value}-${q.value}-${symbolGroupId.value}-${page.value}`,
    () => $api<{ data: SymbolPivotRow[]; meta: { current_page: number; last_page: number } }>('/symbols/pivots', {
        query: { period: period.value, q: q.value, symbol_group_id: symbolGroupId.value, page: page.value },
    }),
    { watch: [period, q, symbolGroupId, page] }
)

const rows = computed(() => data.value?.data || [])
const meta = computed(() => data.value?.meta)

const searchInput = ref(q.value)

function applySearch() {
    router.push({ query: { ...route.query, q: searchInput.value, page: undefined } })
}

function changeGroup(event: Event) {
    const value = (event.target as HTMLSelectElement).value
    router.push({ query: { ...route.query, symbol_group_id: value || undefined, page: undefined } })
}

function changePeriod(value: string) {
    router.push({ query: { ...route.query, period: value } })
}
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">پیووت پوینت نمادها</h1>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <input
                        v-model="searchInput"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="جست‌وجوی نماد یا نام شرکت..."
                        @keyup.enter="applySearch"
                    >
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select form-select-sm" :value="symbolGroupId" @change="changeGroup">
                        <option value="">همه گروه‌ها</option>
                        <option v-for="g in symbolGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-1">
                    <button class="btn btn-sm btn-outline-secondary w-100" @click="applySearch">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div class="col-12 col-md-4">
                    <div class="btn-group w-100" role="group">
                        <button
                            v-for="(label, value) in periodOptions"
                            :key="value"
                            type="button"
                            class="btn btn-sm"
                            :class="period === value ? 'btn-gold' : 'btn-outline-secondary'"
                            @click="changePeriod(value)"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">سطوح پیووت پوینت {{ periodOptions[period] }}</div>
        <div class="table-responsive">
          <LoadingSpinner v-if="pending" size="sm" />
          <template v-else>
            <table class="table table-hover align-middle mb-0 tabular-nums">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>نام کامل</th>
                        <th v-for="(label, key) in levelLabels" :key="key">{{ label }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="rows.length === 0">
                        <td :colspan="9" class="text-center text-muted py-4">نمادی یافت نشد.</td>
                    </tr>
                    <tr v-for="row in rows" :key="row.id">
                        <td>
                            <NuxtLink :to="`/symbols/${row.id}`" class="fw-semibold text-decoration-none">
                                {{ row.ticker }}
                            </NuxtLink>
                        </td>
                        <td class="text-muted">{{ row.name }}</td>
                        <template v-if="row.levels">
                            <td v-for="(_, key) in levelLabels" :key="key">
                                {{ row.levels[key].value.toLocaleString('fa-IR') }}
                                <PercentBadge :percent="row.levels[key].percent" :is-above="row.levels[key].is_above" />
                            </td>
                        </template>
                        <template v-else>
                            <td v-for="(_, key) in levelLabels" :key="key" class="text-muted">—</td>
                        </template>
                    </tr>
                </tbody>
            </table>
          </template>
        </div>
    </div>

    <Pagination
        v-if="meta"
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        @change="(p) => router.push({ query: { ...route.query, page: p } })"
    />
</template>
