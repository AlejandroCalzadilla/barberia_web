<script setup>
import { ref, watch } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  clientes: Array,
  barberos: Array,
  servicios: Array,
})

const form = useForm({
  id_cliente: '',
  id_barbero: '',
  id_servicio: '',
  fecha_reserva: '',
  hora_inicio: '',
  notas: '',
  tipo_pago: 'anticipo',
  metodo_pago: 'efectivo',
  notas_pago: '',
})

const precioServicio = ref(0)
const montoAnticipo = ref(0)
const horariosDisponibles = ref([])
const cargandoHorarios = ref(false)

function onServicioChange() {
  const s = props.servicios.find(x => x.id_servicio === form.id_servicio)
  if (s && s.precio != null) {
    precioServicio.value = s.precio
    montoAnticipo.value = s.precio * 0.50 // Anticipo 50%
  }
  cargarHorariosDisponibles()
}

async function cargarHorariosDisponibles() {
  if (!form.id_barbero || !form.fecha_reserva) {
    horariosDisponibles.value = []
    return
  }

  cargandoHorarios.value = true
  try {
    const response = await axios.get(route('reservas.horarios-disponibles'), {
      params: {
        barbero_id: form.id_barbero,
        fecha: form.fecha_reserva,
        servicio_id: form.id_servicio || null,
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

// Observar cambios en barbero y fecha para actualizar horarios
watch(() => form.id_barbero, cargarHorariosDisponibles)
watch(() => form.fecha_reserva, cargarHorariosDisponibles)

function submit() {
  console.log('Submit function called')
  console.log('Form data:', form.data())
  console.log('Route:', route('reservas.store'))
  
  form.post(route('reservas.store'), {
    onStart: () => console.log('Request starting...'),
    onSuccess: () => console.log('Success!'),
    onError: (errors) => console.error('Validation errors:', errors),
    onFinish: () => console.log('Request finished')
  })
}
</script>

<template>
  <AppLayout title="Nueva reserva">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Nueva reserva</h2>
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
          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Cliente</label>
            <select 
              v-model="form.id_cliente" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="" disabled>Seleccione...</option>
              <option v-for="c in clientes" :key="c.id_cliente" :value="c.id_cliente">{{ c.user?.name }}</option>
            </select>
            <div v-if="form.errors.id_cliente" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_cliente }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Barbero</label>
            <select 
              v-model="form.id_barbero" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="" disabled>Seleccione...</option>
              <option v-for="b in barberos" :key="b.id_barbero" :value="b.id_barbero">{{ b.user?.name }}</option>
            </select>
            <div v-if="form.errors.id_barbero" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_barbero }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Servicio</label>
            <select 
              v-model="form.id_servicio" 
              @change="onServicioChange" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="" disabled>Seleccione...</option>
              <option v-for="s in servicios" :key="s.id_servicio" :value="s.id_servicio">{{ s.nombre }}</option>
            </select>
            <div v-if="form.errors.id_servicio" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_servicio }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Fecha</label>
            <input 
              v-model="form.fecha_reserva" 
              type="date" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.fecha_reserva" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.fecha_reserva }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Hora inicio</label>
            <select 
              v-model="form.hora_inicio" 
              :disabled="cargandoHorarios || horariosDisponibles.length === 0"
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition disabled:opacity-30"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="" disabled>
                {{ cargandoHorarios ? 'Cargando horarios...' : horariosDisponibles.length === 0 ? 'No hay horarios disponibles' : 'Seleccione...' }}
              </option>
              <option v-for="horario in horariosDisponibles" :key="horario.hora" :value="horario.hora">
                {{ horario.label }}
              </option>
            </select>
            <div v-if="form.errors.hora_inicio" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.hora_inicio }}</div>
            <div v-if="!form.id_barbero || !form.fecha_reserva" class="text-xs mt-1" style="color: var(--color-neutral); opacity: 0.7;">
              Seleccione barbero y fecha para ver horarios disponibles
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Tipo de pago</label>
            <select 
              v-model="form.tipo_pago" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="anticipo">Anticipo (50%)</option>
              <option value="completo">Pago completo</option>
            </select>
            <div v-if="form.errors.tipo_pago" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.tipo_pago }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Método de pago</label>
            <select 
              v-model="form.metodo_pago" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="efectivo">Efectivo</option>
              <option value="tarjeta">Tarjeta</option>
              <option value="transferencia">Transferencia</option>
              <option value="qr">QR</option>
            </select>
            <div v-if="form.errors.metodo_pago" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.metodo_pago }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Notas</label>
            <textarea 
              v-model="form.notas" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}" 
              rows="2"
              placeholder="Notas de la reserva (opcional)"
            ></textarea>
            <div v-if="form.errors.notas" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.notas }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Notas del pago</label>
            <textarea 
              v-model="form.notas_pago" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}" 
              rows="2"
              placeholder="Notas del pago (opcional)"
            ></textarea>
            <div v-if="form.errors.notas_pago" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.notas_pago }}</div>
          </div>

          <!-- Resumen de la reserva -->
          <div v-if="precioServicio > 0" class="md:col-span-2 p-4 rounded border" style="background-color: var(--color-base-light); border-color: var(--color-primary);">
            <h3 class="font-semibold mb-2" style="color: var(--color-primary);">📋 Resumen de la reserva</h3>
            <div class="grid gap-2 text-sm" style="color: var(--color-neutral);">
              <div class="flex justify-between">
                <span>Precio del servicio:</span>
                <span class="font-semibold">Bs. {{ Number(precioServicio).toFixed(2) }}</span>
              </div>
              <div v-if="form.tipo_pago === 'anticipo'" class="flex justify-between">
                <span>Anticipo (50%):</span>
                <span class="font-semibold" style="color: var(--color-primary);">Bs. {{ Number(montoAnticipo).toFixed(2) }}</span>
              </div>
              <div v-if="form.tipo_pago === 'anticipo'" class="flex justify-between">
                <span>Saldo restante:</span>
                <span class="font-semibold">Bs. {{ Number(precioServicio - montoAnticipo).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between border-t pt-2 mt-2" style="border-color: var(--color-primary-light);">
                <span>Estado de la reserva:</span>
                <span class="font-semibold px-2 py-1 rounded" style="background-color: var(--color-primary); color: white;">Confirmada</span>
              </div>
            </div>
          </div>

          <div class="md:col-span-2 flex gap-2 mt-2">
            <button 
              type="button"
              @click.prevent="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 text-white rounded hover:opacity-90 transition disabled:opacity-50"
              style="background-color: var(--color-primary);">
              {{ form.processing ? 'Guardando...' : 'Guardar' }}
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
