<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface AlertLogItem {
    id: number
    symbol: { ticker: string; name: string }
    price_at_trigger: number
    channel: 'telegram' | 'sms' | 'web'
    status: 'sent' | 'failed'
    sent_at: string
}

const { $api } = useNuxtApp()
const route = useRoute()

const page = computed(() => Number(route.query.page) || 1)

const { data } = await useAsyncData(
    () => `alert-logs-${page.value}`,
    () => $api<{ data: AlertLogItem[]; meta: { current_page: number; last_page: number } }>(
        '/alert-logs',
        { query: { page: page.value } }
    ),
    { watch: [page] }
)

const logs = computed(() => data.value?.data || [])
const meta = computed(() => data.value?.meta)

const channelLabels: Record<string, string> = { telegram: 'تلگرام', sms: 'پیامک', web: 'وب' }
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">تاریخچه آلرت‌ها</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header">آلرت‌های ارسال‌شده</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>قیمت لحظه‌ی فعال‌شدن</th>
                        <th>کانال</th>
                        <th>وضعیت</th>
                        <th>زمان</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="logs.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">هنوز آلرتی ارسال نشده است.</td>
                    </tr>
                    <tr v-for="log in logs" :key="log.id">
                        <td class="fw-semibold">{{ log.symbol.ticker }}</td>
                        <td class="tabular-nums">{{ log.price_at_trigger.toLocaleString('fa-IR') }}</td>
                        <td>{{ channelLabels[log.channel] }}</td>
                        <td>
                            <span v-if="log.status === 'sent'" class="badge bg-positive-tint text-positive">ارسال‌شده</span>
                            <span v-else class="badge bg-negative-tint text-negative">ناموفق</span>
                        </td>
                        <td class="tabular-nums">{{ new Date(log.sent_at).toLocaleString('fa-IR') }}</td>
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
