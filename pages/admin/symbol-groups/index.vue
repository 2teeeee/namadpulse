<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'admin' })

interface GroupItem { id: number; name: string; code: string; parent: string | null; symbols_count: number; is_active: boolean }

const { $api } = useNuxtApp()

const route = useRoute()
const page = computed(() => Number(route.query.page) || 1)

const { data, refresh } = await useAsyncData(
    () => `admin-symbol-groups-${page.value}`,
    () => $api<{ data: GroupItem[]; meta: { current_page: number; last_page: number } }>('/admin/symbol-groups', {
      query: { page: page.value },
    }),
    { watch: [page] }
)
const groups = computed(() => data.value?.data || [])
const meta = computed(() => data.value?.meta)

const form = reactive({ name: '', code: '' })
const errorMessage = ref('')
const creating = ref(false)

async function createGroup() {
    errorMessage.value = ''
    creating.value = true

    try {
        await $api('/admin/symbol-groups', { method: 'POST', body: form })
        form.name = ''
        form.code = ''
        await refresh()
    } catch (error: any) {
        errorMessage.value = error?.data?.message || 'خطا در ایجاد گروه.'
    } finally {
        creating.value = false
    }
}

async function toggleActive(group: GroupItem) {
    await $api(`/admin/symbol-groups/${group.id}`, {
        method: 'PUT',
        body: { name: group.name, is_active: !group.is_active },
    })
    await refresh()
}

async function deleteGroup(id: number) {
    if (!confirm('حذف این گروه انجام شود؟')) return
    await $api(`/admin/symbol-groups/${id}`, { method: 'DELETE' })
    await refresh()
}
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">گروه نمادها</h1>
        </div>
    </div>

    <div class="card mb-4" style="max-width: 560px;">
        <div class="card-header">گروه جدید</div>
        <div class="card-body p-4">
            <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>
            <form class="row g-2" @submit.prevent="createGroup">
                <div class="col-7">
                    <input v-model="form.name" type="text" class="form-control" placeholder="نام گروه" required>
                </div>
                <div class="col-3">
                    <input v-model="form.code" type="text" class="form-control tabular-nums" placeholder="کد" required>
                </div>
                <div class="col-2">
                    <button type="submit" class="btn btn-gold w-100" :disabled="creating">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">فهرست گروه‌ها</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نام</th>
                        <th>کد</th>
                        <th>والد</th>
                        <th>تعداد نماد</th>
                        <th>وضعیت</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="group in groups" :key="group.id">
                        <td class="fw-semibold">{{ group.name }}</td>
                        <td class="tabular-nums">{{ group.code }}</td>
                        <td class="text-muted">{{ group.parent || '—' }}</td>
                        <td class="tabular-nums">{{ group.symbols_count }}</td>
                        <td>
                            <span v-if="group.is_active" class="badge bg-positive-tint text-positive">فعال</span>
                            <span v-else class="badge bg-negative-tint text-negative">غیرفعال</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" @click="toggleActive(group)">
                                {{ group.is_active ? 'غیرفعال کن' : 'فعال کن' }}
                            </button>
                            <button class="btn btn-sm btn-outline-danger" @click="deleteGroup(group.id)">
                                <i class="bi bi-trash"></i>
                            </button>
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
