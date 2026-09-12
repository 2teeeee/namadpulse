export default defineNuxtRouteMiddleware(async () => {
    const authStore = useAuthStore()
    authStore.hydrate()

    if (!authStore.isLoggedIn) {
        return navigateTo('/login')
    }

    if (!authStore.user) {
        await authStore.fetchMe()
    }

    if (!authStore.isAdmin) {
        return navigateTo('/dashboard')
    }
})
