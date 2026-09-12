<script setup lang="ts">
definePageMeta({ layout: 'guest', middleware: 'guest' })

const authStore = useAuthStore()

const mobile = ref('')
const errorMessage = ref('')
const loading = ref(false)

async function submit() {
    errorMessage.value = ''
    loading.value = true

    try {
        await authStore.sendOtp(mobile.value)
        await navigateTo({ path: '/login/verify', query: { mobile: mobile.value } })
    } catch (error: any) {
        errorMessage.value = error?.data?.message || 'خطایی رخ داد. دوباره تلاش کنید.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <h1 class="h5 mb-1">ورود به حساب کاربری</h1>
    <p class="text-muted mb-4" style="font-size: 0.85rem;">
        شماره موبایل خود را وارد کنید تا کد ورود برایتان پیامک شود.
    </p>

    <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="mobile" class="form-label">شماره موبایل</label>
            <input
                id="mobile"
                v-model="mobile"
                type="tel"
                inputmode="numeric"
                pattern="09[0-9]{9}"
                maxlength="11"
                class="form-control tabular-nums"
                placeholder="09xxxxxxxxx"
                autofocus
                required
            >
        </div>

        <button type="submit" class="btn btn-gold w-100" :disabled="loading">
            {{ loading ? 'در حال ارسال...' : 'دریافت کد ورود' }}
        </button>
    </form>
</template>
