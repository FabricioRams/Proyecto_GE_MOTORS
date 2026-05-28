<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
      <div>
        <img src="/logo.png" alt="G&E Motors" class="mx-auto h-12 w-auto" />
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Iniciar Sesión</h2>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email-address" class="sr-only">Correo electrónico</label>
            <input
              id="email-address"
              v-model="form.email"
              type="email"
              required
              class="appearance-none rounded-t-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              :class="{ 'border-red-500': errors.email }"
              placeholder="Correo electrónico"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Contraseña</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="appearance-none rounded-b-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              :class="{ 'border-red-500': errors.password }"
              placeholder="Contraseña"
            />
          </div>
        </div>

        <div v-if="errors.general" class="text-sm text-red-600 text-center">
          {{ errors.general }}
        </div>

        <BaseButton variant="primary" size="lg" class="w-full" :loading="loading" type="submit">
          Iniciar Sesión
        </BaseButton>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificacionesStore } from '@/stores/notificaciones'
import { useDataFetch } from '@/composables/useDataFetch'
import { authService } from '@/services/authService'
import BaseButton from '@/components/shared/BaseButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const notificacionesStore = useNotificacionesStore()

const form = reactive({ email: '', password: '' })
const loading = ref(false)
const errors = ref({})

// Composable for login request
const { loading: loginLoading, data: loginData, error: loginError, execute: loginRequest } =
  useDataFetch((payload) => authService.login(payload))

async function handleLogin() {
  loading.value = true
  errors.value = {}

  try {
    // Execute the login request via the composable
    const response = await loginRequest(form)
    
    // Update the auth store with the response data
    authStore.token = response.data.token
    authStore.user = response.data.usuario
    authStore.isAuthenticated = true
    localStorage.setItem('gem_motors_token', response.data.token)

    const redirect = router.currentRoute.value.query.redirect || '/'
    router.push(redirect)
  } catch (err) {
    // Error already handled by composable for notifications, but we need to set field errors if any
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      errors.value = { general: err.message || 'Credenciales inválidas' }
    }

    // Show a general notification (the composable already shows one, but we can also show a specific one if needed)
    notificacionesStore.addNotification({
      type: 'error',
      message: errors.value.general || 'Error al intentar iniciar sesión',
      timeout: 5000
    })
  } finally {
    loading.value = false
  }
}
</script>