<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold text-gray-900">
        Lector OBD-II
      </h2>
      <span class="text-sm text-gray-500">
        Escanea la ECU del vehículo para leer códigos de falla
      </span>
    </div>

    <!-- Selector de Escenario de Prueba (Solo en Desarrollo) -->
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
      <label class="block text-sm font-medium text-amber-800 mb-2">Simulador de Fallas (Escenario de prueba)</label>
      <select v-model="selectedScenario" class="w-full px-3 py-2 border-amber-300 rounded-md text-sm focus:ring-amber-500 focus:border-amber-500">
        <option value="43 01 03 00">P0103 - Entrada Alta Sensor Masa Aire (MAF)</option>
        <option value="43 01 00 00">P0000 - Fallos Aleatorios / Sin fallas críticas</option>
        <option value="43 03 00 01 00 00">P0300, P0100 - Fallas múltiples (Encendido + MAF)</option>
        <option value="43 01 30 00">P0130 - Sensor Oxígeno (Banco 1, Sensor 1)</option>
        <option value="43 40 10 00">B0010 - Falla en Iluminación Interior</option>
        <option value="CUSTOM">Ingresar trama manual...</option>
      </select>
      <input v-if="selectedScenario === 'CUSTOM'" v-model="customHex" type="text" placeholder="Ej: 43 01 03 00" class="mt-2 w-full px-3 py-2 border rounded-md text-mono" />
    </div>

    <!-- Estado de escaneo -->
    <div v-if="scanning" class="text-center py-8">
      <div class="flex items-center justify-center space-x-4">
        <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-indigo-600 font-medium">Leyendo datos de la ECU...</p>
      </div>
    </div>

    <!-- Botón de escaneo -->
    <div v-if="!scanning && results.length === 0" class="text-center py-8">
      <BaseButton
        variant="primary"
        size="lg"
        @click="startScan"
      >
        Iniciar Escaneo de la ECU
      </BaseButton>
    </div>

    <!-- Resultados -->
    <div v-else-if="results && results.length > 0" class="mt-6">
      <h3 class="text-xl font-bold text-gray-900 mb-4">
        Códigos de Falla Detectados
      </h3>
      <div class="space-y-3">
        <div
          v-for="(dtc, index) in results"
          :key="index"
          class="p-4 bg-white rounded-lg shadow-md border-l-4"
          :class="{ 'border-indigo-500': dtc.tipo === 'P', 'border-blue-500': dtc.tipo === 'B', 'border-green-500': dtc.tipo === 'C', 'border-yellow-500': dtc.tipo === 'U' }"
        >
          <div class="flex justify-between items-start">
            <div>
              <p class="text-lg font-semibold text-indigo-600">
                {{ dtc.codigo }}
              </p>
              <p class="text-sm text-gray-500">
                {{ dtc.sistema || 'Sistema no especificado' }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-600">
                Severidad
              </p>
              <p class="text-lg font-bold" :class="{
                'text-red-600': dtc.severidad === 'Alta',
                'text-yellow-600': dtc.severidad === 'Media',
                'text-green-600': dtc.severidad === 'Baja'
              }">
                {{ dtc.severidad }}
              </p>
            </div>
          </div>
          <p class="mt-3 text-base text-gray-700 leading-relaxed">
            {{ dtc.descripcion }}
          </p>
        </div>
      </div>
    </div>

    <!-- Estado vacío -->
    <div v-else-if="results && results.length === 0" class="text-center py-8">
      <p class="text-gray-500">
        No se detectaron códigos de falla.
      </p>
    </div>

    <!-- Errores -->
    <div v-if="error" class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 text-sm text-red-700">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { diagnosticoService } from '@/services/diagnosticoService'
import BaseButton from '@/components/shared/BaseButton.vue'

const scanning = ref(false)
const emit = defineEmits(['scan-completed'])
const results = ref([])
const lastRawHex = ref('')
const selectedScenario = ref('43 01 03 00')
const customHex = ref('')
const error = ref(null)

defineExpose({
  results,
  lastRawHex,
  hasRawHex: computed(() => !!lastRawHex.value),
  reset: () => { results.value = []; lastRawHex.value = ''; }
})

// Simulate a delay for the scanning animation (3 seconds)
function simulateScanDelay() {
  return new Promise(resolve => setTimeout(resolve, 3000))
}

// Start the scan process
async function startScan() {
  scanning.value = true
  error.value = null
  results.value = []

  try {
    // Simulate reading from the bus (3 seconds delay)
    await simulateScanDelay()

    // Example RAW hex string that will produce a DTC in our backend's OBDService
    const rawHex = selectedScenario.value === 'CUSTOM' ? customHex.value : selectedScenario.value
    lastRawHex.value = rawHex

    // Send the RAW hex to the backend to parse and get full DTC details
    const response = await diagnosticoService.parsearTramaHex(rawHex)

    if (response.success) {
      // We assume the response.data is an array of DTC objects with:
      // { codigo, descripcion, sistema, tipo }
      const dtcs = response.data

      // Compute severidad based on tipo
      results.value = dtcs.map(dtc => {
        let severidad = 'N/A'
        if (dtc.tipo === 'P' || dtc.tipo === 'U') {
          severidad = 'Alta'
        } else if (dtc.tipo === 'B' || dtc.tipo === 'C') {
          severidad = 'Media'
        }
        return {
          ...dtc,
          severidad
        }
      })
      emit('scan-completed', results.value)
    } else {
      throw new Error(response.mensaje || 'Error al procesar las tramas')
    }
  } catch (err) {
    error.value = err.message || 'Error inesperado durante el escaneo'
  } finally {
    scanning.value = false
  }
}
</script>