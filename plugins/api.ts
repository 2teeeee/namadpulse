export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()

  const api = $fetch.create({
    baseURL: config.public.apiBase,

    onRequest({ options }) {
      const token = useCookie('auth_token').value

      if (token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${token}`,
        }
      }
    },

    async onResponseError({ response }) {
      // توکن نامعتبر/منقضی -> کاربر را به صفحه‌ی ورود برگردان
      if (response.status === 401) {
        const cookie = useCookie('auth_token')
        cookie.value = null
        await navigateTo('/login')
      }
    },
  })

  return {
    provide: { api },
  }
})
