<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface SymbolGroupOption { id: number; name: string }
interface WatchlistOption { id: number; name: string }
interface SymbolResult { id: number; ticker: string; name: string; group: string | null; last_price: number | null; change_percent: number | null }

type ThreeState = 'any' | 'above' | 'below'
type CrossState = 'any' | 'bullish' | 'bearish'

const { $api } = useNuxtApp()

const periods = ['year', 'quarter', 'month'] as const
const periodLabels: Record<string, string> = { year: 'سالانه', quarter: 'فصلی', month: 'ماهانه' }
const pivotLabels: Record<string, string> = { pp: 'PP', r1: 'R1', r2: 'R2', r3: 'R3', s1: 'S1', s2: 'S2', s3: 'S3' }
const averageLabels: Record<string, string> = { ma_20: 'میانگین ۲۰ روزه', ema_100: 'میانگین نمایی ۱۰۰ روزه', ema_200: 'میانگین نمایی ۲۰۰ روزه' }
const crossLabels: Record<string, string> = { '5_20': '۵ به ۲۰', '5_60': '۵ به ۶۰', '20_60': '۲۰ به ۶۰' }

const threeStateOptions: Record<ThreeState, string> = { any: 'هر چیزی', above: 'بالا', below: 'پایین' }
const crossStateOptions: Record<CrossState, string> = { any: 'هر چیزی', bullish: 'صعودی', bearish: 'نزولی' }

const pivotFilters = reactive<Record<string, Record<string, ThreeState>>>(
    Object.fromEntries(periods.map((p) => [p, Object.fromEntries(Object.keys(pivotLabels).map((l) => [l, 'any']))]))
)
const averageFilters = reactive<Record<string, ThreeState>>(
    Object.fromEntries(Object.keys(averageLabels).map((k) => [k, 'any']))
)
const priceCrossFilters = reactive<Record<string, CrossState>>(
    Object.fromEntries(Object.keys(crossLabels).map((k) => [k, 'any']))
)
const volumeCrossFilters = reactive<Record<string, CrossState>>(
    Object.fromEntries(Object.keys(crossLabels).map((k) => [k, 'any']))
)
const symbolGroupId = ref('')

const { data: groupsData } = await useAsyncData('symbol-groups', () => $api<{ data: SymbolGroupOption[] }>('/symbol-groups'))
const symbolGroups = computed(() => groupsData.value?.data || [])

const { data: watchlistsData } = await useAsyncData('watchlists-for-screener', () => $api<{ data: WatchlistOption[] }>('/watchlists'))
const watchlists = computed(() => watchlistsData.value?.data || [])
const selectedWatchlistId = ref<number | null>(null)

const results = ref<SymbolResult[]>([])
const meta = ref<{ current_page: number; last_page: number; total: number } | null>(null)
const searched = ref(false)
const loading = ref(false)
const selectedIds = ref<number[]>([])
const bulkMessage = ref('')

function buildQuery(page = 1): Record<string, string> {
    const query: Record<string, string> = { page: String(page) }

    for (const period of periods) {
        for (const level of Object.keys(pivotLabels)) {
            const value = pivotFilters[period][level]
            if (value !== 'any') query[`pivot[${period}][${level}]`] = value
        }
    }

    for (const key of Object.keys(averageLabels)) {
        if (averageFilters[key] !== 'any') query[`average[${key}]`] = averageFilters[key]
    }

    for (const key of Object.keys(crossLabels)) {
        if (priceCrossFilters[key] !== 'any') query[`price_cross[${key}]`] = priceCrossFilters[key]
        if (volumeCrossFilters[key] !== 'any') query[`volume_cross[${key}]`] = volumeCrossFilters[key]
    }

    if (symbolGroupId.value) query['symbol_group_id'] = symbolGroupId.value

    return query
}

async function runSearch(page = 1) {
    loading.value = true
    bulkMessage.value = ''

    try {
        const response = await $api<{ data: SymbolResult[]; meta: typeof meta.value }>('/screener', {
            query: buildQuery(page),
        })
        results.value = response.data
        meta.value = response.meta
        searched.value = true
        selectedIds.value = []
    } finally {
        loading.value = false
    }
}

function toggleSelectAll(event: Event) {
    const checked = (event.target as HTMLInputElement).checked
    selectedIds.value = checked ? results.value.map((r) => r.id) : []
}

async function addToWatchlist() {
    if (!selectedWatchlistId.value) {
        bulkMessage.value = 'یک واچ‌لیست انتخاب کنید.'
        return
    }

    const body: Record<string, any> = { watchlist_id: selectedWatchlistId.value }
    if (selectedIds.value.length > 0) {
        body.symbol_ids = selectedIds.value
    }
    // اگر هیچ نمادی تیک نخورده، بدون symbol_ids می‌فرستیم -> سرور کل نتایج فیلترشده را اضافه می‌کند
    Object.assign(body, buildQuery(1))

    const response = await $api<{ message: string }>('/screener/add-to-watchlist', {
        method: 'POST',
        body,
    })

    bulkMessage.value = response.message
}
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">فیلتر واچ‌لیست (اسکرینر)</h1>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label">گروه نماد</label>
                <select v-model="symbolGroupId" class="form-select" style="max-width: 320px;">
                    <option value="">همه گروه‌ها</option>
                    <option v-for="g in symbolGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                </select>
            </div>

            <div v-for="period in periods" :key="period">
                <h2 class="h6 mb-3">نسبت به سطوح پیووت پوینت {{ periodLabels[period] }}</h2>
                <div class="row g-3 mb-4">
                    <div v-for="(label, key) in pivotLabels" :key="key" class="col-6 col-md-3">
                        <label class="form-label" style="font-size: 0.82rem;">{{ label }}</label>
                        <select v-model="pivotFilters[period][key]" class="form-select form-select-sm">
                            <option v-for="(optLabel, value) in threeStateOptions" :key="value" :value="value">{{ optLabel }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <h2 class="h6 mb-3">نسبت به میانگین‌ها</h2>
            <div class="row g-3 mb-4">
                <div v-for="(label, key) in averageLabels" :key="key" class="col-6 col-md-4">
                    <label class="form-label" style="font-size: 0.82rem;">{{ label }}</label>
                    <select v-model="averageFilters[key]" class="form-select form-select-sm">
                        <option v-for="(optLabel, value) in threeStateOptions" :key="value" :value="value">{{ optLabel }}</option>
                    </select>
                </div>
            </div>

            <h2 class="h6 mb-3">کراس میانگین قیمت</h2>
            <div class="row g-3 mb-4">
                <div v-for="(label, key) in crossLabels" :key="key" class="col-6 col-md-4">
                    <label class="form-label" style="font-size: 0.82rem;">{{ label }}</label>
                    <select v-model="priceCrossFilters[key]" class="form-select form-select-sm">
                        <option v-for="(optLabel, value) in crossStateOptions" :key="value" :value="value">{{ optLabel }}</option>
                    </select>
                </div>
            </div>

            <h2 class="h6 mb-3">کراس میانگین حجم</h2>
            <div class="row g-3 mb-4">
                <div v-for="(label, key) in crossLabels" :key="key" class="col-6 col-md-4">
                    <label class="form-label" style="font-size: 0.82rem;">{{ label }}</label>
                    <select v-model="volumeCrossFilters[key]" class="form-select form-select-sm">
                        <option v-for="(optLabel, value) in crossStateOptions" :key="value" :value="value">{{ optLabel }}</option>
                    </select>
                </div>
            </div>

            <button type="button" class="btn btn-gold" :disabled="loading" @click="runSearch(1)">
                <i class="bi bi-funnel"></i>
                {{ loading ? 'در حال جست‌وجو...' : 'اعمال فیلتر' }}
            </button>
        </div>
    </div>

    <div v-if="searched" class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>{{ meta?.total ?? 0 }} نماد یافت شد</span>

            <div class="d-flex align-items-center gap-2">
                <select v-model="selectedWatchlistId" class="form-select form-select-sm" style="width: auto;">
                    <option :value="null">انتخاب واچ‌لیست...</option>
                    <option v-for="w in watchlists" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
                <button type="button" class="btn btn-sm btn-gold" @click="addToWatchlist">
                    افزودن به واچ‌لیست
                </button>
            </div>
        </div>

        <div v-if="bulkMessage" class="alert alert-success m-3 py-2">{{ bulkMessage }}</div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" class="form-check-input" @change="toggleSelectAll"></th>
                        <th>نماد</th>
                        <th>گروه</th>
                        <th>آخرین قیمت</th>
                        <th>تغییر</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="results.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">نمادی با این شرایط یافت نشد.</td>
                    </tr>
                    <tr v-for="symbol in results" :key="symbol.id">
                        <td><input v-model="selectedIds" type="checkbox" class="form-check-input" :value="symbol.id"></td>
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
                    </tr>
                </tbody>
            </table>
        </div>

        <nav v-if="meta && meta.last_page > 1" class="p-3">
            <ul class="pagination mb-0">
                <li v-for="p in meta.last_page" :key="p" class="page-item" :class="{ active: p === meta.current_page }">
                    <button type="button" class="page-link" @click="runSearch(p)">{{ p }}</button>
                </li>
            </ul>
        </nav>
    </div>
</template>
