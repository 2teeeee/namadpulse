<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'admin' })

interface ImportRunItem {
    id: number
    status: 'pending' | 'running' | 'completed' | 'failed'
    ticker_filter: string | null
    total_symbols: number | null
    processed_symbols: number
    success_count: number
    skipped_count: number
    progress_percent: number
    is_finished: boolean
    triggered_by: string | null
    started_at: string | null
    finished_at: string | null
}

const { $api } = useNuxtApp()

const { data, refresh } = await useAsyncData('admin-imports', () =>
    $api<{ data: ImportRunItem[]; latest_run: ImportRunItem | null }>('/admin/imports')
)

const runs = computed(() => data.value?.data || [])
const activeRun = ref<ImportRunItem | null>(data.value?.latest_run || null)

const ticker = ref('')
const withIndicators = ref(true)
const submitting = ref(false)

let pollTimer: ReturnType<typeof setInterval> | null = null

function startPolling(runId: number) {
    stopPolling()
    pollTimer = setInterval(async () => {
        const response = await $api<{ data: ImportRunItem }>(`/admin/imports/${runId}/status`)
        activeRun.value = response.data

        if (response.data.is_finished) {
            stopPolling()
            await refresh()
        }
    }, 2500)
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer)
        pollTimer = null
    }
}

onUnmounted(stopPolling)

if (activeRun.value && !activeRun.value.is_finished) {
    startPolling(activeRun.value.id)
}

async function startImport() {
    submitting.value = true

    try {
        const response = await $api<{ data: ImportRunItem }>('/admin/imports', {
            method: 'POST',
            body: { ticker: ticker.value || null, with_indicators: withIndicators.value },
        })
        activeRun.value = response.data
        startPolling(response.data.id)
    } finally {
        submitting.value = false
    }
}

const statusLabels: Record<string, string> = { pending: 'در صف', running: 'در حال اجرا', completed: 'تکمیل‌شده', failed: 'ناموفق' }
const statusClasses: Record<string, string> = {
    pending: 'bg-secondary-subtle text-muted',
    running: 'bg-gold-tint',
    completed: 'bg-positive-tint text-positive',
    failed: 'bg-negative-tint text-negative',
}
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Import قیمت روزانه</h1>
        </div>
    </div>

    <div class="card mb-4" style="max-width: 640px;">
        <div class="card-header">شروع Import جدید</div>
        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label">فقط یک نماد (اختیاری)</label>
                <input v-model="ticker" type="text" class="form-control" placeholder="مثلاً فولاد — خالی برای همه نمادهای فعال">
            </div>

            <div class="mb-4 form-check form-switch">
                <input id="withIndicators" v-model="withIndicators" type="checkbox" class="form-check-input">
                <label for="withIndicators" class="form-check-label">بعد از import، اندیکاتورها هم بازمحاسبه شوند</label>
            </div>

            <button
                type="button"
                class="btn btn-gold"
                :disabled="submitting || (activeRun !== null && !activeRun.is_finished)"
                @click="startImport"
            >
                <i class="bi bi-cloud-arrow-down"></i>
                شروع Import
            </button>
        </div>
    </div>

    <div v-if="activeRun && !activeRun.is_finished" class="card mb-4" style="max-width: 640px;">
        <div class="card-header">در حال اجرا...</div>
        <div class="card-body p-4">
            <div class="progress mb-2" style="height: 10px;">
                <div class="progress-bar bg-warning" :style="{ width: activeRun.progress_percent + '%' }"></div>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size: 0.8rem;">
                <span>{{ activeRun.processed_symbols }} از {{ activeRun.total_symbols ?? '?' }} نماد</span>
                <span>{{ activeRun.progress_percent }}٪</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">تاریخچه‌ی Importها</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>تاریخ شروع</th>
                        <th>فیلتر نماد</th>
                        <th>وضعیت</th>
                        <th>موفق / رد‌شده</th>
                        <th>اجراکننده</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="runs.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">هنوز importی اجرا نشده است.</td>
                    </tr>
                    <tr v-for="run in runs" :key="run.id">
                        <td class="tabular-nums">
                            {{ run.started_at ? new Date(run.started_at).toLocaleString('fa-IR') : '—' }}
                        </td>
                        <td>{{ run.ticker_filter || 'همه نمادها' }}</td>
                        <td>
                            <span class="badge" :class="statusClasses[run.status]" :style="run.status === 'running' ? 'color: var(--gold-600);' : ''">
                                {{ statusLabels[run.status] }}
                            </span>
                        </td>
                        <td class="tabular-nums">{{ run.success_count }} / {{ run.skipped_count }}</td>
                        <td class="text-muted">{{ run.triggered_by || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
