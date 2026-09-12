export default defineNuxtRouteMiddleware(async () => {
    const authStore = useAuthStore()
    authStore.hydrate()

    if (!authStore.isLoggedIn) {
        return navigateTo('/login')
    }

    if (!authStore.isAdmin) {
        return navigateTo('/dashboard')
    }
})
