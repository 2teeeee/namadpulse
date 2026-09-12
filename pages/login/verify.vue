<script setup lang="ts">
definePageMeta({ layout: 'guest', middleware: 'guest' })

const route = useRoute()
const authStore = useAuthStore()

const mobile = (route.query.mobile as string) || ''
const code = ref('')
const errorMessage = ref('')
const loading = ref(false)

if (!mobile) {
    await navigateTo('/login')
}

async function submit() {
    errorMessage.value = ''
    loading.value = true

    try {
        await authStore.verifyOtp(mobile, code.value)
        await navigateTo('/dashboard')
    } catch (error: any) {
        errorMessage.value = error?.data?.message || 'کد وارد‌شده صحیح نیست.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <h1 class="h5 mb-1">کد ورود را وارد کنید</h1>
    <p class="text-muted mb-4" style="font-size: 0.85rem;">
        کد ۶ رقمی برای شماره
        <span class="tabular-nums fw-semibold">{{ mobile }}</span>
        پیامک شد.
    </p>

    <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="code" class="form-label">کد تایید</label>
            <input
                id="code"
                v-model="code"
                type="text"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                class="form-control tabular-nums text-center"
                style="letter-spacing: 0.5rem; font-size: 1.25rem;"
                autofocus
                required
            >
        </div>

        <button type="submit" class="btn btn-gold w-100 mb-2" :disabled="loading">
            {{ loading ? 'در حال بررسی...' : 'ورود' }}
        </button>

        <NuxtLink to="/login" class="btn btn-outline-secondary w-100">
            ویرایش شماره موبایل
        </NuxtLink>
    </form>
</template>
