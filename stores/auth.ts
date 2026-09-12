import { defineStore } from 'pinia'

interface AuthUser {
  id: number
  name: string
  mobile: string
  is_active: boolean
  roles: string[]
  is_admin: boolean
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null as string | null,
    user: null as AuthUser | null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    isAdmin: (state) => !!state.user?.is_admin,
  },

  actions: {
    // در ابتدای هر صفحه، توکن را از کوکی بازیابی می‌کند (سازگار با SSR)
    hydrate() {
      const cookie = useCookie<string | null>('auth_token')
      this.token = cookie.value
    },

    async sendOtp(mobile: string) {
      const { $api } = useNuxtApp()
      await $api('/auth/login/send-otp', { method: 'POST', body: { mobile } })
    },

    async verifyOtp(mobile: string, code: string) {
      const { $api } = useNuxtApp()
      const response = await $api<{ token: string; user: AuthUser }>('/auth/login/verify-otp', {
        method: 'POST',
        body: { mobile, code },
      })

      this.token = response.token
      this.user = response.user

      const cookie = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 30 })
      cookie.value = response.token
    },

    async fetchMe() {
      const { $api } = useNuxtApp()
      const response = await $api<{ user: AuthUser }>('/auth/me')
      this.user = response.user
    },

    async logout() {
      const { $api } = useNuxtApp()

      try {
        await $api('/auth/logout', { method: 'POST' })
      } finally {
        this.token = null
        this.user = null
        const cookie = useCookie('auth_token')
        cookie.value = null
      }
    },
  },
})
