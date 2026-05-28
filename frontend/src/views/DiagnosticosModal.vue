<template>
  <BaseModal :show="show" @close="$emit('update:show', false)">
    <template #header>
      <h3 class="text-lg font-medium text-gray-900">
        Historial de Diagnósticos: {{ vehicle?.placa }}
      </h3>
    </template>

    <div v-if="loading" class="text-center py-6">Cargando historial...</div>
    
    <div v-else-if="diagnosticos.length === 0" class="text-center py-6 text-gray-500">
      No se registran diagnósticos previos para este vehículo.
    </div>

    <div v-else class="space-y-4">
      <div v-for="d in diagnosticos" :key="d.id" class="border rounded-lg p-4 bg-gray-50">
        <div class="flex justify-between text-xs text-gray-500 mb-2">
          <span>{{ formatDate(d.created_at) }}</span>
          <span class="font-bold">OT #{{ d.orden_id }}</span>
        </div>
        <div class="flex flex-wrap gap-2 mb-2">
          <span 
            v-for="code in d.codigos_falla" 
            :key="code"
            class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-sm font-mono font-bold"
          >
            {{ code }}
          </span>
        </div>
        <p class="text-sm text-gray-700 italic">"{{ d.observaciones }}"</p>
      </div>
    </div>

    <template #footer>
      <BaseButton variant="outline" @click="$emit('update:show', false)">Cerrar</BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { vehiculoService } from '@/services/vehiculoService'
import { useDataFetch } from '@/composables/useDataFetch'
import BaseModal from '@/components/shared/BaseModal.vue'
import BaseButton from '@/components/shared/BaseButton.vue'

const props = defineProps({
  show: Boolean,
  vehicle: Object
})

const diagnosticos = ref([])
const { loading, execute: fetchHistory } = useDataFetch((id) => vehiculoService.getDiagnosticos(id))

watch(() => props.show, async (isShown) => {
  if (isShown && props.vehicle) {
    try {
      const response = await fetchHistory(props.vehicle.id)
      if (response && response.success) {
        diagnosticos.value = response.data
      }
    } catch (err) {
      diagnosticos.value = []
    }
  }
})

function formatDate(dateStr) {
  if (!dateStr) return 'Fecha no disponible'
  // Reemplazar espacio por T para asegurar compatibilidad ISO en todos los navegadores
  const d = new Date(dateStr.replace(' ', 'T'))
  return isNaN(d.getTime()) 
    ? 'Fecha inválida' 
    : d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>