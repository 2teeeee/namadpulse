<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface SymbolOption { id: number; ticker: string; name: string }
interface AlertRuleItem {
    id: number
    symbol: { id: number; ticker: string; name: string }
    type_label: string
    condition_value: number
    status: 'active' | 'triggered' | 'disabled'
    notify_via: string[]
}

const { $api } = useNuxtApp()

const { data, refresh } = await useAsyncData('alert-rules', () =>
    $api<{ data: AlertRuleItem[] }>('/alert-rules')
)
const alertRules = computed(() => data.value?.data || [])

// برای فرم ساخت، لیست نمادها رو صفحه‌به‌صفحه نمی‌گیریم؛ کاربر با جست‌وجو نماد رو پیدا می‌کنه
const symbolQuery = ref('')
const symbolOptions = ref<SymbolOption[]>([])
const selectedSymbolId = ref<number | null>(null)

const typeOptions = [
    { value: 'price_above', label: 'قیمت بالاتر از' },
    { value: 'price_below', label: 'قیمت پایین‌تر از' },
    { value: 'percent_change_up', label: 'رشد درصدی' },
    { value: 'percent_change_down', label: 'افت درصدی' },
    { value: 'volume_spike', label: 'جهش حجم' },
]

const form = reactive({
    type: 'price_above',
    condition_value: '',
    notify_via: ['web'] as string[],
})

const errorMessage = ref('')
const creating = ref(false)

watch(symbolQuery, async (value) => {
    if (value.length < 2) {
        symbolOptions.value = []
        return
    }
    const res = await $api<{ data: SymbolOption[] }>('/symbols', { query: { q: value } })
    symbolOptions.value = res.data
})

function selectSymbol(symbol: SymbolOption) {
    selectedSymbolId.value = symbol.id
    symbolQuery.value = `${symbol.ticker} — ${symbol.name}`
    symbolOptions.value = []
}

async function createRule() {
    errorMessage.value = ''

    if (!selectedSymbolId.value) {
        errorMessage.value = 'یک نماد از لیست انتخاب کنید.'
        return
    }

    creating.value = true

    try {
        await $api('/alert-rules', {
            method: 'POST',
            body: {
                symbol_id: selectedSymbolId.value,
                type: form.type,
                condition_value: Number(form.condition_value),
                notify_via: form.notify_via,
            },
        })

        selectedSymbolId.value = null
        symbolQuery.value = ''
        form.condition_value = ''
        await refresh()
    } catch (error: any) {
        errorMessage.value = error?.data?.message || 'خطا در ایجاد قانون آلرت.'
    } finally {
        creating.value = false
    }
}

async function toggleStatus(rule: AlertRuleItem) {
    const newStatus = rule.status === 'active' ? 'disabled' : 'active'
    await $api(`/alert-rules/${rule.id}`, { method: 'PUT', body: { status: newStatus } })
    await refresh()
}

async function deleteRule(id: number) {
    if (!confirm('این قانون آلرت حذف شود؟')) return
    await $api(`/alert-rules/${id}`, { method: 'DELETE' })
    await refresh()
}
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">قوانین آلرت</h1>
        </div>
    </div>

    <div class="card mb-4" style="max-width: 640px;">
        <div class="card-header">آلرت جدید</div>
        <div class="card-body p-4">
            <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>

            <form @submit.prevent="createRule">
                <div class="mb-3 position-relative">
                    <label class="form-label">نماد</label>
                    <input v-model="symbolQuery" type="text" class="form-control" placeholder="جست‌وجوی نماد..." required>
                    <div v-if="symbolOptions.length" class="list-group position-absolute w-100 shadow-sm" style="z-index: 10;">
                        <button
                            v-for="option in symbolOptions"
                            :key="option.id"
                            type="button"
                            class="list-group-item list-group-item-action"
                            @click="selectSymbol(option)"
                        >
                            {{ option.ticker }} — {{ option.name }}
                        </button>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">نوع شرط</label>
                        <select v-model="form.type" class="form-select">
                            <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">مقدار</label>
                        <input v-model="form.condition_value" type="number" step="0.01" class="form-control tabular-nums" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">کانال‌های اطلاع‌رسانی</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input id="notify-web" v-model="form.notify_via" type="checkbox" value="web" class="form-check-input">
                            <label for="notify-web" class="form-check-label">وب</label>
                        </div>
                        <div class="form-check">
                            <input id="notify-telegram" v-model="form.notify_via" type="checkbox" value="telegram" class="form-check-input">
                            <label for="notify-telegram" class="form-check-label">تلگرام</label>
                        </div>
                        <div class="form-check">
                            <input id="notify-sms" v-model="form.notify_via" type="checkbox" value="sms" class="form-check-input">
                            <label for="notify-sms" class="form-check-label">پیامک</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold" :disabled="creating">ایجاد آلرت</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">آلرت‌های من</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>شرط</th>
                        <th>مقدار</th>
                        <th>وضعیت</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="alertRules.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">هنوز آلرتی نساخته‌اید.</td>
                    </tr>
                    <tr v-for="rule in alertRules" :key="rule.id">
                        <td class="fw-semibold">{{ rule.symbol.ticker }}</td>
                        <td>{{ rule.type_label }}</td>
                        <td class="tabular-nums">{{ rule.condition_value.toLocaleString('fa-IR') }}</td>
                        <td>
                            <span v-if="rule.status === 'active'" class="badge bg-positive-tint text-positive">فعال</span>
                            <span v-else-if="rule.status === 'triggered'" class="badge bg-gold-tint" style="color: var(--gold-600);">فعال‌شده</span>
                            <span v-else class="badge bg-secondary-subtle text-muted">غیرفعال</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" @click="toggleStatus(rule)">
                                {{ rule.status === 'active' ? 'غیرفعال کن' : 'فعال کن' }}
                            </button>
                            <button class="btn btn-sm btn-outline-danger" @click="deleteRule(rule.id)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
