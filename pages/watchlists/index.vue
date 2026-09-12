<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface WatchlistItem { id: number; name: string; symbols_count: number }

const { $api } = useNuxtApp()

const { data, refresh } = await useAsyncData('watchlists', () =>
    $api<{ data: WatchlistItem[] }>('/watchlists')
)
const watchlists = computed(() => data.value?.data || [])

const newName = ref('')
const creating = ref(false)
const errorMessage = ref('')

async function createWatchlist() {
    errorMessage.value = ''
    creating.value = true

    try {
        await $api('/watchlists', { method: 'POST', body: { name: newName.value } })
        newName.value = ''
        await refresh()
    } catch (error: any) {
        errorMessage.value = error?.data?.message || 'خطا در ایجاد واچ‌لیست.'
    } finally {
        creating.value = false
    }
}

async function deleteWatchlist(id: number) {
    if (!confirm('حذف این واچ‌لیست انجام شود؟')) return
    await $api(`/watchlists/${id}`, { method: 'DELETE' })
    await refresh()
}
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">واچ‌لیست‌های من</h1>
        </div>
    </div>

    <div class="card mb-4" style="max-width: 480px;">
        <div class="card-body p-3">
            <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>
            <form class="d-flex gap-2" @submit.prevent="createWatchlist">
                <input v-model="newName" type="text" class="form-control" placeholder="نام واچ‌لیست جدید" required>
                <button type="submit" class="btn btn-gold text-nowrap" :disabled="creating">
                    <i class="bi bi-plus-lg"></i> ایجاد
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">فهرست واچ‌لیست‌ها</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نام</th>
                        <th>تعداد نمادها</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="watchlists.length === 0">
                        <td colspan="3" class="text-center text-muted py-4">هنوز واچ‌لیستی نساخته‌اید.</td>
                    </tr>
                    <tr v-for="w in watchlists" :key="w.id">
                        <td>
                            <NuxtLink :to="`/watchlists/${w.id}`" class="fw-semibold text-decoration-none">
                                {{ w.name }}
                            </NuxtLink>
                        </td>
                        <td class="tabular-nums">{{ w.symbols_count }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger" @click="deleteWatchlist(w.id)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
