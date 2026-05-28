<template>
  <BaseModal :show="show" @close="handleClose">
    <template #header>
      <h3 class="text-lg font-medium text-gray-900">
        {{ data ? 'Editar Vehículo' : 'Registrar Nuevo Vehículo' }}
      </h3>
    </template>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Placa</label>
        <input 
          v-model="form.placa" 
          type="text" 
          class="w-full px-3 py-2 border rounded-md uppercase" 
          placeholder="ABC-123"
          required 
        />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Marca</label>
          <input v-model="form.marca" type="text" class="w-full px-3 py-2 border rounded-md" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Modelo</label>
          <input v-model="form.modelo" type="text" class="w-full px-3 py-2 border rounded-md" required />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Propietario</label>
        <select v-model="form.cliente_id" class="w-full px-3 py-2 border rounded-md" required>
          <option value="">Seleccione un cliente...</option>
          <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nombre }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">VIN (Nro. Chasis)</label>
        <input v-model="form.vin" type="text" class="w-full px-3 py-2 border rounded-md uppercase" placeholder="Ej: 1HGCM82633A004352" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Color</label>
        <input v-model="form.color" type="text" class="w-full px-3 py-2 border rounded-md" placeholder="Ej: Blanco, Gris, Azul..." />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Año (Opcional)</label>
        <input 
          v-model.number="form.anio" 
          type="number" 
          class="w-full px-3 py-2 border rounded-md" 
          min="1900" 
          :max="new Date().getFullYear() + 1" 
        />
      </div>
    </form>

    <template #footer>
      <BaseButton variant="outline" @click="handleClose">Cancelar</BaseButton>
      <BaseButton variant="primary" :loading="loading" @click="handleSubmit">
        {{ data ? 'Actualizar' : 'Guardar' }}
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { vehiculoService } from '@/services/vehiculoService'
import { useDataFetch } from '@/composables/useDataFetch'
import BaseModal from '@/components/shared/BaseModal.vue'
import BaseButton from '@/components/shared/BaseButton.vue'

const props = defineProps({
  show: Boolean,
  data: Object,
  clients: Array
})

const emit = defineEmits(['update:show', 'saved', 'close'])

const form = reactive({
  placa: '',
  marca: '',
  modelo: '',
  cliente_id: '',
  anio: null,
  vin: '',
  color: ''
})

const { loading, execute: saveVehiculo } = useDataFetch(
  (payload) => props.data ? vehiculoService.update(props.data.id, payload) : vehiculoService.create(payload)
)

watch(() => props.data, (val) => {
  if (val) {
    Object.assign(form, val)
  } else {
    resetForm()
  }
}, { immediate: true })

async function handleSubmit() {
  try {
    await saveVehiculo(form)
    emit('saved')
    handleClose()
  } catch (err) {
    // Error handled by composable
  }
}

function handleClose() {
  emit('update:show', false)
  emit('close')
  if (!props.data) resetForm()
}

function resetForm() {
  form.placa = ''
  form.marca = ''
  form.modelo = ''
  form.cliente_id = ''
  form.anio = null
  form.vin = ''
  form.color = ''
}
</script>