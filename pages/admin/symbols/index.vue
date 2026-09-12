<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'admin' })

interface SymbolGroupOption { id: number; name: string }
interface SymbolItem {
    id: number; ticker: string; name: string; name_en: string | null
    isin_code: string | null; tsetmc_id: string | null; board: string
    shares_count: number | null; is_active: boolean
    group: { id: number; name: string } | null
}

const { $api } = useNuxtApp()

const q = ref('')
const symbolGroupId = ref('')
const route = useRoute()
const router = useRouter()
const page = computed(() => Number(route.query.page) || 1)

const { data, refresh, pending } = await useAsyncData(
    () => `admin-symbols-${q.value}-${symbolGroupId.value}-${page.value}`,
    () => $api<{ data: SymbolItem[]; meta: { current_page: number; last_page: number } }>('/admin/symbols', {
      query: { q: q.value, symbol_group_id: symbolGroupId.value, page: page.value },
    }),
    { watch: [q, symbolGroupId, page] }
)
const symbols = computed(() => data.value?.data || [])
const meta = computed(() => data.value?.meta)

const { data: groupsData } = await useAsyncData('symbol-groups-admin', () => $api<{ data: SymbolGroupOption[] }>('/symbol-groups'))
const symbolGroups = computed(() => groupsData.value?.data || [])

const boardOptions: Record<string, string> = { bourse: 'بازار اول/دوم بورس', farabourse: 'فرابورس', other: 'سایر' }

const modalOpen = ref(false)
const editingId = ref<number | null>(null)
const errorMessage = ref('')
const saving = ref(false)

const form = reactive({
    symbol_group_id: '',
    ticker: '',
    name: '',
    name_en: '',
    isin_code: '',
    tsetmc_id: '',
    board: 'bourse',
    shares_count: '',
    is_active: true,
})

function openCreate() {
    editingId.value = null
    Object.assign(form, {
        symbol_group_id: '', ticker: '', name: '', name_en: '',
        isin_code: '', tsetmc_id: '', board: 'bourse', shares_count: '', is_active: true,
    })
    errorMessage.value = ''
    modalOpen.value = true
}

function openEdit(symbol: SymbolItem) {
    editingId.value = symbol.id
    Object.assign(form, {
        symbol_group_id: symbol.group?.id || '',
        ticker: symbol.ticker,
        name: symbol.name,
        name_en: symbol.name_en || '',
        isin_code: symbol.isin_code || '',
        tsetmc_id: symbol.tsetmc_id || '',
        board: symbol.board,
        shares_count: symbol.shares_count || '',
        is_active: symbol.is_active,
    })
    errorMessage.value = ''
    modalOpen.value = true
}

async function save() {
    errorMessage.value = ''
    saving.value = true

    try {
        if (editingId.value) {
            await $api(`/admin/symbols/${editingId.value}`, { method: 'PUT', body: form })
        } else {
            await $api('/admin/symbols', { method: 'POST', body: form })
        }
        modalOpen.value = false
        await refresh()
    } catch (error: any) {
        errorMessage.value = error?.data?.message || 'خطا در ذخیره‌سازی.'
    } finally {
        saving.value = false
    }
}

async function deleteSymbol(id: number) {
    if (!confirm('با حذف این نماد، تاریخچه قیمت و آلرت‌های مرتبط هم حذف می‌شود. ادامه می‌دهید؟')) return
    await $api(`/admin/symbols/${id}`, { method: 'DELETE' })
    await refresh()
}
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">نمادها</h1>
        </div>
        <div>
            <button class="btn btn-gold btn-sm" @click="openCreate">
                <i class="bi bi-plus-lg"></i> نماد جدید
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <input v-model="q" type="text" class="form-control form-control-sm" placeholder="جست‌وجوی نماد یا نام شرکت...">
                </div>
                <div class="col-12 col-md-6">
                    <select v-model="symbolGroupId" class="form-select form-select-sm">
                        <option value="">همه گروه‌ها</option>
                        <option v-for="g in symbolGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">فهرست نمادها</div>
        <div class="table-responsive">
          <LoadingSpinner v-if="pending" size="sm" />

          <template v-else>
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نماد</th>
                        <th>نام شرکت</th>
                        <th>گروه</th>
                        <th>بازار</th>
                        <th>وضعیت</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="pending">
                        <td colspan="6" class="text-center text-muted py-4">در حال بارگذاری...</td>
                    </tr>
                    <tr v-else-if="symbols.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">نمادی یافت نشد.</td>
                    </tr>
                    <tr v-for="symbol in symbols" :key="symbol.id">
                        <td class="fw-semibold">{{ symbol.ticker }}</td>
                        <td>{{ symbol.name }}</td>
                        <td class="text-muted">{{ symbol.group?.name }}</td>
                        <td>{{ boardOptions[symbol.board] }}</td>
                        <td>
                            <span v-if="symbol.is_active" class="badge bg-positive-tint text-positive">فعال</span>
                            <span v-else class="badge bg-negative-tint text-negative">غیرفعال</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" @click="openEdit(symbol)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" @click="deleteSymbol(symbol.id)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
          </template>
        </div>
    </div>

    <div v-if="modalOpen" class="modal d-block" style="background: rgba(0,0,0,0.4);" @click.self="modalOpen = false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ editingId ? 'ویرایش نماد' : 'نماد جدید' }}</h5>
                    <button type="button" class="btn-close" @click="modalOpen = false"></button>
                </div>
                <div class="modal-body">
                    <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">نماد (ticker)</label>
                            <input v-model="form.ticker" type="text" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">گروه نماد</label>
                            <select v-model="form.symbol_group_id" class="form-select" required>
                                <option value="">— انتخاب کنید —</option>
                                <option v-for="g in symbolGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">نام کامل شرکت</label>
                            <input v-model="form.name" type="text" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">نام لاتین (name_en)</label>
                            <input v-model="form.name_en" type="text" class="form-control">
                            <div class="form-text">باید دقیقاً با نام فایل txt قیمت‌ها یکی باشد.</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">کد ISIN</label>
                            <input v-model="form.isin_code" type="text" class="form-control tabular-nums">
                        </div>
                        <div class="col-6">
                            <label class="form-label">شناسه TSETMC</label>
                            <input v-model="form.tsetmc_id" type="text" class="form-control tabular-nums">
                        </div>
                        <div class="col-6">
                            <label class="form-label">بازار</label>
                            <select v-model="form.board" class="form-select">
                                <option v-for="(label, value) in boardOptions" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">تعداد سهام</label>
                            <input v-model="form.shares_count" type="number" class="form-control tabular-nums">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input id="symbolActive" v-model="form.is_active" type="checkbox" class="form-check-input">
                                <label for="symbolActive" class="form-check-label">نماد فعال است</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" @click="modalOpen = false">انصراف</button>
                    <button type="button" class="btn btn-gold" :disabled="saving" @click="save">ذخیره</button>
                </div>
            </div>
        </div>
    </div>

    <Pagination
        v-if="meta"
        :current-page="meta.current_page"
        :last-page="meta.last_page"
        @change="(p) => router.push({ query: { ...route.query, page: p } })"
    />
</template>
