<script setup lang="ts">
definePageMeta({ layout: 'default', middleware: 'auth' })

interface SymbolListItem {
    id: number
    ticker: string
    name: string
    group: string | null
    last_price: number | null
    change_percent: number | null
}

interface DashboardData {
    active_symbols_count: number
    top_gainers: SymbolListItem[]
    top_losers: SymbolListItem[]
}

const authStore = useAuthStore()
const { $api } = useNuxtApp()

if (!authStore.user) {
    await authStore.fetchMe()
}

const { data, pending } = await useAsyncData('dashboard', () => $api<{ data: DashboardData }>('/dashboard'))
const dashboard = computed(() => data.value?.data)
</script>

<template>
    <div class="page-header">
        <div>
            <h1 class="page-header__title">داشبورد</h1>
        </div>
    </div>

  <LoadingSpinner v-if="pending" />

  <template v-else>
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-4">
        <StatCard label="نمادهای فعال بازار" :value="dashboard?.active_symbols_count ?? '—'" />
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card">
          <div class="card-header">پرتحرک‌ترین‌های صعودی امروز</div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
              <tr>
                <th>نماد</th>
                <th>آخرین قیمت</th>
                <th>تغییر</th>
              </tr>
              </thead>
              <tbody>
              <tr v-if="!dashboard?.top_gainers?.length">
                <td colspan="3" class="text-center text-muted py-4">داده‌ای موجود نیست.</td>
              </tr>
              <tr v-for="item in dashboard?.top_gainers" :key="item.id">
                <td>
                  <NuxtLink :to="`/symbols/${item.id}`" class="fw-semibold text-decoration-none">
                    {{ item.ticker }}
                  </NuxtLink>
                </td>
                <td class="tabular-nums">{{ item.last_price?.toLocaleString('fa-IR') }}</td>
                <td class="text-positive tabular-nums">
                  <i class="bi bi-arrow-up"></i> {{ item.change_percent }}٪
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card">
          <div class="card-header">پرتحرک‌ترین‌های نزولی امروز</div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
              <tr>
                <th>نماد</th>
                <th>آخرین قیمت</th>
                <th>تغییر</th>
              </tr>
              </thead>
              <tbody>
              <tr v-if="!dashboard?.top_losers?.length">
                <td colspan="3" class="text-center text-muted py-4">داده‌ای موجود نیست.</td>
              </tr>
              <tr v-for="item in dashboard?.top_losers" :key="item.id">
                <td>
                  <NuxtLink :to="`/symbols/${item.id}`" class="fw-semibold text-decoration-none">
                    {{ item.ticker }}
                  </NuxtLink>
                </td>
                <td class="tabular-nums">{{ item.last_price?.toLocaleString('fa-IR') }}</td>
                <td class="text-negative tabular-nums">
                  <i class="bi bi-arrow-down"></i> {{ item.change_percent }}٪
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </template>


</template>
