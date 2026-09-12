export default defineNuxtRouteMiddleware(() => {
  const authStore = useAuthStore()
  authStore.hydrate()

  if (!authStore.isLoggedIn) {
    return navigateTo('/login')
  }
})
