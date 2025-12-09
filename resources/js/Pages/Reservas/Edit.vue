<script setup>
import { ref, watch } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  reserva: Object,
  clientes: Array,
  barberos: Array,
  servicios: Array,
})

const form = useForm({
  fecha_reserva: props.reserva.fecha_reserva ?? '',
  hora_inicio: props.reserva.hora_inicio ?? '',
  estado: props.reserva.estado ?? 'confirmada',
})

const horariosDisponibles = ref([])
const cargandoHorarios = ref(false)

async function cargarHorariosDisponibles() {
  if (!props.reserva.id_barbero || !form.fecha_reserva) {
    horariosDisponibles.value = []
    return
  }

  cargandoHorarios.value = true
  try {
    const response = await axios.get(route('reservas.horarios-disponibles'), {
      params: {
        barbero_id: props.reserva.id_barbero,
        fecha: form.fecha_reserva,
        servicio_id: props.reserva.id_servicio || null,
      }
    })
    horariosDisponibles.value = response.data.horarios
    
    // Limpiar hora seleccionada si ya no está disponible
    if (form.hora_inicio && !horariosDisponibles.value.find(h => h.hora === form.hora_inicio)) {
      form.hora_inicio = ''
    }
  } catch (error) {
    console.error('Error al cargar horarios:', error)
    horariosDisponibles.value = []
  } finally {
    cargandoHorarios.value = false
  }
}

// Cargar horarios al montar y cuando cambie la fecha
watch(() => form.fecha_reserva, cargarHorariosDisponibles)

// Cargar horarios iniciales
cargarHorariosDisponibles()

function submit() {
  form.put(route('reservas.update', props.reserva.id_reserva))
}
</script>

<template>
  <AppLayout :title="'Editar reserva #' + (props.reserva?.id_reserva || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Editar reserva</h2>
        <Link 
          :href="route('reservas.index')" 
          class="px-3 py-2 rounded hover:opacity-90 transition"
          style="background-color: var(--color-secondary); color: var(--color-base);">
          Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-6 grid gap-4 md:grid-cols-2" style="background-color: var(--color-base);">
          
          <!-- Información de solo lectura -->
          <div class="md:col-span-2 p-4 rounded border" style="background-color: var(--color-base-light); border-color: var(--color-primary);">
            <h3 class="font-semibold mb-3 text-lg" style="color: var(--color-primary);">📋 Información de la reserva</h3>
            <div class="grid gap-3 md:grid-cols-2 text-sm" style="color: var(--color-neutral);">
              <div>
                <span class="font-medium">Cliente:</span>
                <span class="ml-2">{{ reserva.cliente?.user?.name }}</span>
              </div>
              <div>
                <span class="font-medium">Barbero:</span>
                <span class="ml-2">{{ reserva.barbero?.user?.name }}</span>
              </div>
              <div>
                <span class="font-medium">Servicio:</span>
                <span class="ml-2">{{ reserva.servicio?.nombre }}</span>
              </div>
            </div>
          </div>

          <!-- Campos editables: Fecha y Horario -->
          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Fecha *</label>
            <input 
              v-model="form.fecha_reserva" 
              type="date" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.fecha_reserva" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.fecha_reserva }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Hora de inicio *</label>
            <select 
              v-model="form.hora_inicio" 
              :disabled="cargandoHorarios || horariosDisponibles.length === 0"
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="" disabled>{{ cargandoHorarios ? 'Cargando horarios...' : horariosDisponibles.length === 0 ? 'No hay horarios disponibles' : 'Seleccione...' }}</option>
              <option v-for="h in horariosDisponibles" :key="h.hora" :value="h.hora">{{ h.label }}</option>
            </select>
            <div v-if="form.errors.hora_inicio" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.hora_inicio }}</div>
          </div>

          <!-- Información de solo lectura (continuación) -->
          <div class="md:col-span-2 p-4 rounded border" style="background-color: var(--color-base-light); border-color: var(--color-primary);">
            <h3 class="font-semibold mb-3 text-lg" style="color: var(--color-primary);">💰 Información de pago</h3>
            <div class="grid gap-3 md:grid-cols-2 text-sm" style="color: var(--color-neutral);">
              <div>
                <span class="font-medium">Duración:</span>
                <span class="ml-2">{{ reserva.hora_inicio }} - {{ reserva.hora_fin }}</span>
              </div>
              <div>
                <span class="font-medium">Total:</span>
                <span class="ml-2 font-semibold" style="color: var(--color-primary);">Bs. {{ Number(reserva.total).toFixed(2) }}</span>
              </div>
              <div>
                <span class="font-medium">Anticipo (50%):</span>
                <span class="ml-2">Bs. {{ Number(reserva.monto_anticipo).toFixed(2) }}</span>
              </div>
              <div v-if="reserva.notas" class="md:col-span-2">
                <span class="font-medium">Notas:</span>
                <p class="ml-2 mt-1 text-gray-600">{{ reserva.notas }}</p>
              </div>
            </div>
          </div>

          <!-- Pagos relacionados -->
          <div v-if="reserva.pagos?.length" class="md:col-span-2 p-4 rounded border" style="background-color: var(--color-base-light); border-color: var(--color-primary);">
            <h3 class="font-semibold mb-3 text-lg" style="color: var(--color-primary);">💳 Pagos</h3>
            <div class="space-y-2">
              <div v-for="pago in reserva.pagos" :key="pago.id_pago" class="flex justify-between items-center p-2 rounded" style="background-color: var(--color-base);">
                <div class="text-sm" style="color: var(--color-neutral);">
                  <span class="font-medium capitalize">{{ pago.tipo_pago?.replace('_', ' ') }}</span>
                  <span class="ml-2">- {{ pago.metodo_pago }}</span>
                  <span v-if="pago.fecha_pago" class="ml-2 text-xs opacity-75">{{ new Date(pago.fecha_pago).toLocaleDateString() }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="font-semibold">Bs. {{ Number(pago.monto_total).toFixed(2) }}</span>
                  <span 
                    class="px-2 py-1 rounded text-xs font-medium"
                    :style="{
                      backgroundColor: pago.estado === 'pagado' ? 'var(--color-primary)' : 'var(--color-warning)',
                      color: 'white'
                    }"
                  >
                    {{ pago.estado }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Campo editable: Estado -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Estado de la reserva *</label>
            <select 
              v-model="form.estado" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="confirmada">✅ Confirmada</option>
              <option value="completada">✔️ Completada</option>
              <option value="cancelada">❌ Cancelada</option>
              <option value="no_asistio">👤 No asistió</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.estado }}</div>
          </div>

          <div class="md:col-span-2 flex gap-2 mt-2">
            <button 
              @click="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 text-white rounded hover:opacity-90 transition disabled:opacity-50"
              style="background-color: var(--color-primary);">
              Guardar cambios
            </button>
            <Link 
              :href="route('reservas.index')" 
              class="px-4 py-2 rounded hover:opacity-90 transition"
              style="background-color: var(--color-secondary); color: var(--color-base);">
              Cancelar
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
