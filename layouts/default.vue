<script setup lang="ts">
const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()
const isSidebarOpen = ref(false)

async function handleLogout() {
    await authStore.logout()
    await navigateTo('/login')
}

function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

watch(() => route.path, () => {
  isSidebarOpen.value = false
})
</script>

<template>
    <div class="app-shell">
      <aside class="app-sidebar" :class="{ 'is-open': isSidebarOpen }">
            <div class="app-sidebar__brand">
                <div class="app-sidebar__brand-mark">جس</div>
                <div class="app-sidebar__brand-text">
                    جلالی سهام
                    <span class="app-sidebar__brand-sub">پایش و آلرت بازار بورس</span>
                </div>
            </div>

          <nav class="app-nav">
            <div class="app-nav__label">پایش بازار</div>
            <NuxtLink to="/dashboard" class="app-nav__item" :class="{ 'is-active': route.path === '/dashboard' }">
              <i class="bi bi-grid-1x2"></i>
              داشبورد
            </NuxtLink>
            <NuxtLink to="/watchlists" class="app-nav__item" :class="{ 'is-active': route.path.startsWith('/watchlists') }">
              <i class="bi bi-star"></i>
              واچ‌لیست‌های من
            </NuxtLink>
            <NuxtLink to="/symbols" class="app-nav__item" :class="{ 'is-active': route.path.startsWith('/symbols') }">
              <i class="bi bi-graph-up-arrow"></i>
              نمادها
            </NuxtLink>
            <NuxtLink to="/symbols/pivots" class="app-nav__item" :class="{ 'is-active': route.path === '/symbols/pivots' }">
              <i class="bi bi-table"></i>
              پیووت پوینت نمادها
            </NuxtLink>
            <NuxtLink to="/symbols/screener" class="app-nav__item" :class="{ 'is-active': route.path === '/symbols/screener' }">
              <i class="bi bi-funnel"></i>
              فیلتر واچ‌لیست
            </NuxtLink>

            <div class="app-nav__label">معاملات</div>
            <NuxtLink to="/alert-rules" class="app-nav__item" :class="{ 'is-active': route.path.startsWith('/alert-rules') }">
              <i class="bi bi-bell"></i>
              قوانین آلرت
            </NuxtLink>
            <NuxtLink to="/alert-logs" class="app-nav__item" :class="{ 'is-active': route.path.startsWith('/alert-logs') }">
              <i class="bi bi-clock-history"></i>
              تاریخچه آلرت‌ها
            </NuxtLink>

            <template v-if="authStore.isAdmin">
              <div class="app-nav__label">مدیریت سیستم</div>
              <NuxtLink to="/admin/users" class="app-nav__item" :class="{ 'is-active': route.path === '/admin/users' }">
                <i class="bi bi-people"></i>
                کاربران
              </NuxtLink>
              <NuxtLink to="/admin/symbol-groups" class="app-nav__item" :class="{ 'is-active': route.path === '/admin/symbol-groups' }">
                <i class="bi bi-diagram-3"></i>
                گروه نمادها
              </NuxtLink>
              <NuxtLink to="/admin/symbols" class="app-nav__item" :class="{ 'is-active': route.path === '/admin/symbols' }">
                <i class="bi bi-graph-up-arrow"></i>
                نمادها
              </NuxtLink>
              <NuxtLink to="/admin/imports" class="app-nav__item" :class="{ 'is-active': route.path === '/admin/imports' }">
                <i class="bi bi-cloud-arrow-down"></i>
                Import قیمت‌ها
              </NuxtLink>
            </template>
          </nav>

            <div class="app-sidebar__footer">
                <button type="button" class="app-nav__item w-100 border-0 bg-transparent text-start" @click="handleLogout">
                    <i class="bi bi-box-arrow-left"></i>
                    خروج از حساب
                </button>
            </div>
        </aside>

        <div v-if="isSidebarOpen" class="sidebar-overlay" @click="isSidebarOpen = false"></div>

        <div class="app-main">
            <header class="app-topbar">
                <div class="d-flex align-items-center gap-2">
                  <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary sidebar-toggle"
                      @click="toggleSidebar"
                      aria-label="باز کردن منو"
                  >
                    <i class="bi bi-list"></i>
                  </button>
                  <button
                      v-if="route.path !== '/dashboard'"
                      type="button"
                      class="btn btn-sm btn-outline-secondary"
                      @click="router.back()"
                  >
                    <i class="bi bi-arrow-right"></i>
                    بازگشت
                  </button>
                  <div v-else></div>
                </div>
                <div class="d-flex align-items-center gap-3" v-if="authStore.user">
                    <div class="app-topbar__user">
                        <div class="app-topbar__user-avatar">
                            {{ authStore.user.name.slice(0, 1) }}
                        </div>
                        <div class="d-none d-sm-block">
                            <div class="fw-semibold" style="font-size: 0.85rem; line-height: 1.3;">
                                {{ authStore.user.name }}
                            </div>
                            <div class="app-topbar__user-role">
                                {{ authStore.isAdmin ? 'مدیر سیستم' : 'معامله‌گر' }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="app-content">
                <slot />
            </main>
        </div>
    </div>
</template>
