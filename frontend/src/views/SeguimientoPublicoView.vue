<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-3xl mx-auto px-4 py-4">
        <div class="flex items-center justify-center">
          <svg class="h-8 w-8 text-indigo-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zm0-15a1.5 1.5 0 01-3 0 1.5 1.5 0 013 0zm0 15V6.75m0 6.75v6.75m6-13.5a1.5 1.5 0 00-3 0 1.5 1.5 0 003 0zm0 15a1.5 1.5 0 00-3 0 1.5 1.5 0 003 0zm0-15V6.75m0 6.75v6.75" />
          </svg>
          <h1 class="text-2xl font-bold text-gray-900">
            G&E Motors - Seguimiento de Orden
          </h1>
        </div>
      </div>
    </header>

    <main class="flex-1 max-w-3xl mx-auto px-4 py-8 w-full">
      <!-- Search Form -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4 text-center">
          Consulta el estado de tu vehículo
        </h2>
        
        <form @submit.prevent="buscarOrden" class="space-y-4">
          <div>
            <label for="codigo_seguimiento" class="block text-sm font-medium text-gray-700 mb-1">
              Código de Seguimiento
            </label>
            <input
              id="codigo_seguimiento"
              v-model="codigo"
              type="text"
              required
              placeholder="Ej: OT-2024-00123"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center text-lg font-mono uppercase tracking-wider"
              :disabled="loading"
              autocomplete="off"
            />
          </div>

          <BaseButton
            type="submit"
            variant="primary"
            size="lg"
            :loading="loading"
            :disabled="!codigo.trim()"
            class="w-full"
          >
            Consultar Estado
          </BaseButton>
        </form>

        <p class="mt-4 text-xs text-gray-500 text-center">
          Ingrese el código único que le proporcionó nuestro taller
        </p>
      </div>

      <!-- Results Section -->
      <div v-if="orden" class="space-y-6">
        <!-- Vehicle Info Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Información del Vehículo
          </h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Marca / Modelo</p>
              <p class="font-medium text-gray-900">
                {{ orden.vehiculo?.marca }} {{ orden.vehiculo?.modelo }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Placa</p>
              <p class="font-medium text-gray-900">{{ orden.vehiculo?.placa }}</p>
            </div>
          </div>
        </div>

        <!-- Progress Pipeline -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Estado del Trabajo
          </h3>
          
          <OrdenEstadoPipeline
            :estado-actual="orden.estado"
            :presupuesto-aprobado="false"
            :ot-id="null"
            @transicion-estado="() => {}"
          />
        </div>

        <!-- Current Status Detail -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-3">
            Estado Actual
          </h3>
          <div class="flex items-center">
            <span :class="[
              'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
              estadoBadgeClass
            ]">
              {{ estadoLabel }}
            </span>
          </div>
          <p class="mt-3 text-sm text-gray-600">
            Fecha de ingreso: {{ formatDate(orden.created_at) }}
          </p>
        </div>

        <!-- Evidence Gallery (sanitized) -->
        <div v-if="evidencias?.length > 0" class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Evidencias del Trabajo
          </h3>
          
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div
              v-for="evidencia in evidencias"
              :key="evidencia.id"
              class="aspect-square"
            >
              <img
                v-if="evidencia.tipo === 'imagen'"
                :src="evidencia.url"
                :alt="'Evidencia del vehículo'"
                class="w-full h-full object-cover rounded-lg"
              >
              <video
                v-else
                :src="evidencia.url"
                class="w-full h-full object-cover rounded-lg"
                muted
              ></video>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9 0h18M12 9l-3 3m0 0l3 3m-3-3h6" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">
              {{ error }}
            </p>
          </div>
        </div>
      </div>

      <!-- Privacy Notice -->
      <div class="mt-8 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m4.5-4.5a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs text-gray-600">
              <strong>Protección de datos:</strong> Esta consulta pública muestra únicamente información técnica del estado del vehículo, protegiendo su información personal y financiera según la Ley de Protección de Datos Personales (Ley 29733).
            </p>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4">
      <div class="max-w-3xl mx-auto px-4 text-center">
        <p class="text-xs text-gray-500">
          © {{ currentYear }} G&E Motors - Taller Automotriz - Tacna, Perú
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ordenService } from '@/services/ordenService'
import { useDataFetch } from '@/composables/useDataFetch'
import BaseButton from '@/components/shared/BaseButton.vue'
import OrdenEstadoPipeline from '@/components/ordenes/OrdenEstadoPipeline.vue'

const route = useRoute()

const codigo = ref('')
const loading = ref(false)
const orden = ref(null)
const evidencias = ref([])
const error = ref(null)

const currentYear = computed(() => new Date().getFullYear())

const estadoLabels = {
  diagnostico: 'En Diagnóstico',
  reparacion: 'En Reparación',
  esperando_repuesto: 'Esperando Repuesto',
  control_calidad: 'En Control de Calidad',
  entregado: 'Entregado'
}

const estadoLabel = computed(() => {
  return estadoLabels[orden.value?.estado] || orden.value?.estado || ''
})

const estadoBadgeClass = computed(() => {
  const classes = {
    diagnostico: 'bg-blue-100 text-blue-800',
    reparacion: 'bg-orange-100 text-orange-800',
    esperando_repuesto: 'bg-yellow-100 text-yellow-800',
    control_calidad: 'bg-purple-100 text-purple-800',
    entregado: 'bg-green-100 text-green-800'
  }
  return classes[orden.value?.estado] || 'bg-gray-100 text-gray-800'
})

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('es-PE')
}

// ===== Data fetching for orden by codigo =====
const { loading: searchLoading, data: searchData, error: searchError, execute: searchOrden } = 
  useDataFetch(() => ordenService.getPorCodigo(codigo.value))

// Update loading state
watch(() => searchLoading.value, (val) => {
  loading.value = val
})

// Update orden and evidencias from composable data
watch(() => searchData.value, (val) => {
  if (val) {
    orden.value = val.data.orden
    evidencias.value = val.data.evidencias || []
  }
})

// Handle error
watch(searchError, (err) => {
  if (err) {
    error.value = err.message || 'Error al consultar el código de seguimiento'
  } else {
    error.value = null
  }
})

async function buscarOrden() {
  if (!codigo.value.trim()) return
  try {
    await searchOrden()
  } catch (err) {
    // Error handled by watcher
  }
}

onMounted(() => {
  if (route.params.codigo) {
    codigo.value = route.params.codigo
    buscarOrden()
  }
})
</script>