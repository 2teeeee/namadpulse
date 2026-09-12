<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface LevelInfo { value: number; percent: number | null; is_above: boolean }
interface AverageInfo { value: number; percent: number | null; is_above: boolean }
interface PivotPeriod { high: number; low: number; close: number; levels: Record<string, LevelInfo> }
interface ZigzagPoint { jalali_date: string; price: number; type: 'peak' | 'trough'; percent: number | null; is_above: boolean }

interface SymbolDetail {
    id: number
    ticker: string
    name: string
    group: string
    last_price: number
    capital: number | null
    market_cap: number | null
    averages: Record<string, AverageInfo | null>
    flags: { above_yearly_pivot: boolean | null; above_ema_200: boolean | null }
    price_crosses: Record<string, boolean | null>
    volume_crosses: Record<string, boolean | null>
    pivot_levels: Record<string, PivotPeriod | null>
    zigzag_points: ZigzagPoint[]
}

const route = useRoute()
const { $api } = useNuxtApp()

const { data } = await useAsyncData(
    `symbol-${route.params.id}`,
    () => $api<{ data: SymbolDetail }>(`/symbols/${route.params.id}`)
)

const symbol = computed(() => data.value?.data)

const periodLabels: Record<string, string> = { year: 'سالانه', quarter: 'فصلی', month: 'ماهانه' }
const levelLabels: Record<string, string> = { pp: 'PP', r1: 'R1', r2: 'R2', r3: 'R3', s1: 'S1', s2: 'S2', s3: 'S3' }
const averageLabels: Record<string, string> = { ma_20: 'میانگین ۲۰ روزه', ema_100: 'میانگین نمایی ۱۰۰ روزه', ema_200: 'میانگین نمایی ۲۰۰ روزه' }
const crossPairs: [string, string, string][] = [
    ['5_20', '5', '20'],
    ['5_60', '5', '60'],
    ['20_60', '20', '60'],
]

function formatNumber(value: number | null | undefined): string {
    if (value === null || value === undefined) return '—'
    return value.toLocaleString('fa-IR')
}

function formatHomt(value: number | null | undefined): string {
    if (!value) return '—'
    return (value / 1e13).toLocaleString('fa-IR', { maximumFractionDigits: 0 }) + ' همت'
}
</script>

<template>
    <template v-if="symbol">
        <div class="page-header">
            <div>
                <h1 class="page-header__title">{{ symbol.ticker }} — {{ symbol.name }}</h1>
            </div>
        </div>

        <!-- خلاصه بالای صفحه -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <StatCard label="آخرین قیمت" :value="formatNumber(symbol.last_price)" />
            </div>
            <div class="col-6 col-lg-3">
                <StatCard label="سرمایه" :value="formatHomt(symbol.capital)" />
            </div>
            <div class="col-6 col-lg-3">
                <StatCard label="ارزش بازار" :value="formatHomt(symbol.market_cap)" />
            </div>
            <div class="col-6 col-lg-3">
                <StatCard label="گروه نماد" :value="symbol.group" />
            </div>
        </div>

        <!-- میانگین‌ها -->
        <div class="row g-3 mb-4">
            <div v-for="(label, key) in averageLabels" :key="key" class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-card__label">{{ label }}</div>
                    <template v-if="symbol.averages[key]">
                        <div class="stat-card__value tabular-nums">{{ formatNumber(symbol.averages[key]!.value) }}</div>
                        <div class="stat-card__delta" :class="symbol.averages[key]!.is_above ? 'is-positive' : 'is-negative'">
                            <i class="bi" :class="symbol.averages[key]!.is_above ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                            {{ symbol.averages[key]!.percent }}٪
                        </div>
                    </template>
                    <div v-else class="stat-card__value text-muted">—</div>
                </div>
            </div>
        </div>

        <!-- نشانه‌های وضعیت -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <span
                v-if="symbol.flags.above_yearly_pivot !== null"
                class="badge px-3 py-2"
                :class="symbol.flags.above_yearly_pivot ? 'bg-positive-tint text-positive' : 'bg-negative-tint text-negative'"
            >
                <i class="bi" :class="symbol.flags.above_yearly_pivot ? 'bi-arrow-up-circle' : 'bi-arrow-down-circle'"></i>
                {{ symbol.flags.above_yearly_pivot ? 'بالای پیووت پوینت سالانه' : 'زیر پیووت پوینت سالانه' }}
            </span>

            <span
                v-if="symbol.flags.above_ema_200 !== null"
                class="badge px-3 py-2"
                :class="symbol.flags.above_ema_200 ? 'bg-positive-tint text-positive' : 'bg-negative-tint text-negative'"
            >
                <i class="bi" :class="symbol.flags.above_ema_200 ? 'bi-arrow-up-circle' : 'bi-arrow-down-circle'"></i>
                {{ symbol.flags.above_ema_200 ? 'بالای میانگین نمایی ۲۰۰ روزه' : 'زیر میانگین نمایی ۲۰۰ روزه' }}
            </span>

            <template v-for="[key, fast, slow] in crossPairs" :key="'price-' + key">
                <span
                    v-if="symbol.price_crosses[key] !== null"
                    class="badge px-3 py-2"
                    :class="symbol.price_crosses[key] ? 'bg-positive-tint text-positive' : 'bg-negative-tint text-negative'"
                >
                    کراس قیمت {{ fast }}-{{ slow }}:
                    {{ symbol.price_crosses[key] ? `میانگین ${fast} بالای ${slow}` : `میانگین ${fast} زیر ${slow}` }}
                </span>
            </template>

            <template v-for="[key, fast, slow] in crossPairs" :key="'volume-' + key">
                <span
                    v-if="symbol.volume_crosses[key] !== null"
                    class="badge px-3 py-2"
                    :class="symbol.volume_crosses[key] ? 'bg-positive-tint text-positive' : 'bg-negative-tint text-negative'"
                >
                    کراس حجم {{ fast }}-{{ slow }}:
                    {{ symbol.volume_crosses[key] ? `میانگین حجم ${fast} بالای ${slow}` : `میانگین حجم ${fast} زیر ${slow}` }}
                </span>
            </template>
        </div>

        <!-- جدول پیووت پوینت -->
        <div class="card mb-4">
            <div class="card-header">سطوح پیووت پوینت</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 tabular-nums">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th v-for="(label, key) in periodLabels" :key="key">{{ label }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold text-muted">بیشترین قیمت</td>
                            <td v-for="(_, key) in periodLabels" :key="key">
                                {{ symbol.pivot_levels[key] ? formatNumber(symbol.pivot_levels[key]!.high) : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">کمترین قیمت</td>
                            <td v-for="(_, key) in periodLabels" :key="key">
                                {{ symbol.pivot_levels[key] ? formatNumber(symbol.pivot_levels[key]!.low) : '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">آخرین قیمت</td>
                            <td v-for="(_, key) in periodLabels" :key="key">
                                {{ symbol.pivot_levels[key] ? formatNumber(symbol.pivot_levels[key]!.close) : '—' }}
                            </td>
                        </tr>
                        <tr v-for="(levelLabel, levelKey) in levelLabels" :key="levelKey">
                            <td class="fw-semibold">{{ levelLabel }}</td>
                            <td v-for="(_, periodKey) in periodLabels" :key="periodKey">
                                <template v-if="symbol.pivot_levels[periodKey]">
                                    {{ formatNumber(symbol.pivot_levels[periodKey]!.levels[levelKey].value) }}
                                    <PercentBadge
                                        :percent="symbol.pivot_levels[periodKey]!.levels[levelKey].percent"
                                        :is-above="symbol.pivot_levels[periodKey]!.levels[levelKey].is_above"
                                    />
                                </template>
                                <template v-else>—</template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- جدول زیگزاگ -->
        <div class="card">
            <div class="card-header">نقاط زیگزاگ (بر اساس MACD)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>تاریخ</th>
                            <th>مبلغ</th>
                            <th>کف / سقف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="symbol.zigzag_points.length === 0">
                            <td colspan="3" class="text-center text-muted py-4">
                                داده‌ی کافی برای محاسبه‌ی زیگزاگ این نماد وجود ندارد.
                            </td>
                        </tr>
                        <tr v-for="point in symbol.zigzag_points" :key="point.jalali_date">
                            <td>{{ point.jalali_date }}</td>
                            <td class="tabular-nums">
                                {{ formatNumber(point.price) }}
                                <PercentBadge :percent="point.percent" :is-above="point.is_above" />
                            </td>
                            <td>
                                <span v-if="point.type === 'peak'" class="badge bg-negative-tint text-negative">سقف</span>
                                <span v-else class="badge bg-positive-tint text-positive">کف</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </template>
</template>
