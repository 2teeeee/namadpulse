<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'admin' })

interface UserItem { id: number; name: string; mobile: string; is_active: boolean; roles: string[] }
interface RoleOption { id: number; name: string; label: string }

const { $api } = useNuxtApp()

const route = useRoute()
const page = computed(() => Number(route.query.page) || 1)

const { data, refresh } = await useAsyncData(
    () => `admin-users-${page.value}`,
    () => $api<{ data: UserItem[]; meta: { current_page: number; last_page: number } }>('/admin/users', {
      query: { page: page.value },
    }),
    { watch: [page] }
)
const users = computed(() => data.value?.data || [])
const meta = computed(() => data.value?.meta)

const editingUser = ref<UserItem | null>(null)
const availableRoles = ref<RoleOption[]>([])
const selectedRoleIds = ref<number[]>([])
const isActive = ref(true)

async function openEdit(user: UserItem) {
    const response = await $api<{ data: UserItem; available_roles: RoleOption[] }>(`/admin/users/${user.id}`)
    editingUser.value = response.data
    availableRoles.value = response.available_roles
    isActive.value = response.data.is_active
    selectedRoleIds.value = response.available_roles.filter((r) => response.data.roles.includes(r.name)).map((r) => r.id)
}

async function saveUser() {
    if (!editingUser.value) return

    await $api(`/admin/users/${editingUser.value.id}`, {
        method: 'PUT',
        body: { is_active: isActive.value, roles: selectedRoleIds.value },
    })

    editingUser.value = null
    await refresh()
}
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">کاربران</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header">فهرست کاربران</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نام</th>
                        <th>موبایل</th>
                        <th>نقش‌ها</th>
                        <th>وضعیت</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td class="fw-semibold">{{ user.name }}</td>
                        <td class="tabular-nums">{{ user.mobile }}</td>
                        <td>{{ user.roles.join('، ') || '—' }}</td>
                        <td>
                            <span v-if="user.is_active" class="badge bg-positive-tint text-positive">فعال</span>
                            <span v-else class="badge bg-negative-tint text-negative">غیرفعال</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" @click="openEdit(user)">
                                <i class="bi bi-pencil"></i> ویرایش
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- مودال ساده ویرایش کاربر -->
    <div v-if="editingUser" class="modal d-block" style="background: rgba(0,0,0,0.4);" @click.self="editingUser = null">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش {{ editingUser.name }}</h5>
                    <button type="button" class="btn-close" @click="editingUser = null"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check form-switch mb-3">
                        <input id="isActive" v-model="isActive" type="checkbox" class="form-check-input">
                        <label for="isActive" class="form-check-label">کاربر فعال است</label>
                    </div>

                    <label class="form-label">نقش‌ها</label>
                    <div v-for="role in availableRoles" :key="role.id" class="form-check">
                        <input
                            :id="`role-${role.id}`"
                            v-model="selectedRoleIds"
                            type="checkbox"
                            class="form-check-input"
                            :value="role.id"
                        >
                        <label :for="`role-${role.id}`" class="form-check-label">{{ role.label }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" @click="editingUser = null">انصراف</button>
                    <button type="button" class="btn btn-gold" @click="saveUser">ذخیره</button>
                </div>
            </div>
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
