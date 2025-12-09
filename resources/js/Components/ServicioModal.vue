<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  servicio: Object,
  required: true,
})

const emit = defineEmits(['close'])

const page = usePage()
const barberos = ref([])
const horarios = ref([])
const cargandoBarberos = ref(true)
const cargandoHorarios = ref(false)

// Formulario
const formData = ref({
  id_barbero: '',
  fecha_reserva: '',
  hora_inicio: '',
})

const errores = ref({})

onMounted(async () => {
  await cargarBarberos()
})

async function cargarBarberos() {
  try {
    const response = await fetch(route('barberos.disponibles.api'))
    barberos.value = await response.json()
  } catch (error) {
    console.error('Error al cargar barberos:', error)
  } finally {
    cargandoBarberos.value = false
  }
}

async function cargarHorarios() {
  if (!formData.value.id_barbero || !formData.value.fecha_reserva) {
    horarios.value = []
    return
  }

  cargandoHorarios.value = true
  try {
    const url = route('horarios.disponibles.api', {
      barbero_id: formData.value.id_barbero,
      fecha: formData.value.fecha_reserva
    })
    const response = await fetch(url)
    horarios.value = await response.json()
    console.log('Horarios disponibles:', horarios.value)
  } catch (error) {
    console.error('Error al cargar horarios:', error)
  } finally {
    cargandoHorarios.value = false
  }
}

function formatoPrecio(precio) {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
  }).format(precio)
}


async function reservar() {
  errores.value = {}

  if (!formData.value.id_barbero) {
    errores.value.id_barbero = 'Selecciona un barbero'
  }
  if (!formData.value.fecha_reserva) {
    errores.value.fecha_reserva = 'Selecciona una fecha'
  }
  if (!formData.value.hora_inicio) {
    errores.value.hora_inicio = 'Selecciona una hora'
  }

  if (Object.keys(errores.value).length > 0) {
    return
  }

  // Redirigir directamente a la página de pago con los datos de la reserva
  router.get(route('pagos.pagar-reserva'), {
    id_servicio: props.servicio.id_servicio,
    id_barbero: formData.value.id_barbero,
    fecha_reserva: formData.value.fecha_reserva,
    hora_inicio: formData.value.hora_inicio,
  })
  
  emit('close')
}

const fechaMinima = computed(() => {
  const fecha = new Date()
  fecha.setDate(fecha.getDate() + 1)
  return fecha.toISOString().split('T')[0]
})
</script>

<template>
  <!-- Overlay -->
  <div class="fixed inset-0 z-40 bg-black bg-opacity-50" @click="emit('close')"></div>

  <!-- Modal -->
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Encabezado -->
      <div class="sticky top-0 border-b border-gray-200  p-6 flex justify-between items-start">
        <h2 class="text-2xl font-bold " style="color: var(--color-neutral);">{{ servicio.nombre }}</h2>
        <button 
          @click="emit('close')"
          class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl"
        >
          ×
        </button>
      </div>

      <!-- Contenido -->
      <div class="p-6 space-y-6">
        <!-- Categoría y descripción -->
        <div>
          <div class="flex items-center gap-2 mb-2">
            <span class="px-3 py-1 rounded-full text-xs font-medium" 
              :style="{ 
                backgroundColor: 'var(--color-primary)', 
                color: 'var(--color-base)' 
              }">
              {{ servicio.categoria?.nombre }}
            </span>
          </div>
          <p class="text-gray-600 dark:text-gray-400">
            {{ servicio.descripcion || 'Sin descripción disponible' }}
          </p>
        </div>

        <!-- Información del servicio -->
        <div class="grid grid-cols-3 gap-4">
          <div class="p-4 rounded" :style="{ backgroundColor: 'var(--color-primary)' }">
            <p class="text-sm " style="color: var(--color-neutral); margin-bottom: 0.25rem;">Precio</p>
            <p class="text-xl font-bold" :style="{ color: 'var(--color-neutral)' }">
              {{ formatoPrecio(servicio.precio) }}
            </p>
          </div>
          <div class="p-4 rounded" :style="{ backgroundColor: 'var(--color-primary)' }">
            <p class="text-sm  mb-1" style="color: var(--color-neutral);">Duración</p>
            <p class="text-xl font-bold" :style="{ color: 'var(--color-neutral)' }">
              {{ servicio.duracion_minutos }} min
            </p>
          </div>
          <!-- <div class="p-4 rounded" :style="{ backgroundColor: 'var(--color-accent)', opacity: 0.1 }">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Disponibilidad</p>
            <p class="text-xl font-bold" :style="{ color: 'var(--color-accent)' }">
              Inmediata
            </p>
          </div> -->
        </div>

        <!-- Formulario de reserva -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reservar cita</h3>

          <form @submit.prevent="reservar" class="space-y-4">
            <!-- Barbero -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Selecciona un barbero
              </label>
              <select 
                v-model="formData.id_barbero"
                @change="cargarHorarios"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2"
                :style="errores.id_barbero ? { borderColor: 'var(--color-error)' } : {}"
                :disabled="cargandoBarberos"
              >
                <option value="">{{ cargandoBarberos ? 'Cargando...' : 'Elige un barbero' }}</option>
                <option v-for="b in barberos" :key="b.id_barbero" :value="b.id_barbero">
                  {{ b.user?.name }}
                </option>
              </select>
              <p v-if="errores.id_barbero" class="text-sm mt-1" style="color: var(--color-error);">
                {{ errores.id_barbero }}
              </p>
            </div>

            <!-- Fecha -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Fecha
              </label>
              <input 
                v-model="formData.fecha_reserva"
                @change="cargarHorarios"
                type="date" 
                :min="fechaMinima"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2"
                :style="errores.fecha_reserva ? { borderColor: 'var(--color-error)' } : {}"
              />
              <p v-if="errores.fecha_reserva" class="text-sm mt-1" style="color: var(--color-error);">
                {{ errores.fecha_reserva }}
              </p>
            </div>

            <!-- Hora -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Hora
              </label>
              <select 
                v-model="formData.hora_inicio"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2"
                :style="errores.hora_inicio ? { borderColor: 'var(--color-error)' } : {}"
                :disabled="cargandoHorarios || horarios.length === 0"
              >
                <option value="">{{ cargandoHorarios ? 'Cargando...' : 'Elige una hora' }}</option>
                <option v-for="hora in horarios" :key="hora" :value="hora">
                  {{ hora }}
                </option>
              </select>
              <p v-if="errores.hora_inicio" class="text-sm mt-1" style="color: var(--color-error);">
                {{ errores.hora_inicio }}
              </p>
              <p v-if="!cargandoHorarios && horarios.length === 0 && formData.id_barbero && formData.fecha_reserva" 
                 class="text-sm mt-1 text-yellow-600 dark:text-yellow-400">
                No hay horarios disponibles para esta fecha
              </p>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button 
                type="button"
                @click="emit('close')"
                class="flex-1 px-4 py-2 rounded border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              >
                Cancelar
              </button>
              <button 
                type="submit"
                class="flex-1 px-4 py-2 rounded text-white font-medium transition-colors"
                :style="{ backgroundColor: 'var(--color-primary)' }">
                Confirmar reserva
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
