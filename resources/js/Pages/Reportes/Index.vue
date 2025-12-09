<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import FullReportsChart from '@/Components/FullReportsChart.vue'
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  pagosData: Array,
  reservasData: Array,
  ingresosMensuales: Object,
  rankingBarberos: Array,
  serviciosMasPopulares: Array,
  clientesFrecuentes: Array,
  distribucionEstados: Object,
  distribucionMetodosPago: Array,
  barberos: Array,
  servicios: Array,
  filtros: Object,
})

console.log('📋 Reportes/Index - Props recibidos:', {
  pagosData: props.pagosData,
  reservasData: props.reservasData,
  ingresosMensuales: props.ingresosMensuales
})

const fechaInicio = ref(props.filtros?.fecha_inicio || new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0])
const fechaFin = ref(props.filtros?.fecha_fin || new Date().toISOString().split('T')[0])
const barberoSeleccionado = ref(props.filtros?.id_barbero || '')
const servicioSeleccionado = ref(props.filtros?.id_servicio || '')
const metodoPagoSeleccionado = ref(props.filtros?.metodo_pago || '')
const estadoReservaSeleccionado = ref(props.filtros?.estado_reserva || '')

function aplicarFiltros() {
  router.get(route('reportes.index'), {
    fecha_inicio: fechaInicio.value,
    fecha_fin: fechaFin.value,
    id_barbero: barberoSeleccionado.value,
    id_servicio: servicioSeleccionado.value,
    metodo_pago: metodoPagoSeleccionado.value,
    estado_reserva: estadoReservaSeleccionado.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function limpiarFiltros() {
  fechaInicio.value = new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0]
  fechaFin.value = new Date().toISOString().split('T')[0]
  barberoSeleccionado.value = ''
  servicioSeleccionado.value = ''
  metodoPagoSeleccionado.value = ''
  estadoReservaSeleccionado.value = ''
  aplicarFiltros()
}
</script>

<template>
  <AppLayout title="Reportes y Estadísticas">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" :style="{ color: 'var(--color-neutral)' }">
          Reportes y Estadísticas
        </h2>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filtros -->
        <div class="shadow sm:rounded-lg p-6 mb-6" :style="{ backgroundColor: 'var(--color-base)' }">
          <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
            Filtros
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Fecha Inicio
              </label>
              <input 
                v-model="fechaInicio" 
                type="date" 
                class="w-full rounded px-3 py-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Fecha Fin
              </label>
              <input 
                v-model="fechaFin" 
                type="date"
                class="w-full rounded px-3 py-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Barbero
              </label>
              <select 
                v-model="barberoSeleccionado"
                class="w-full rounded px-3 py-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }"
              >
                <option value="">Todos</option>
                <option v-for="barbero in barberos" :key="barbero.id_barbero" :value="barbero.id_barbero">
                  {{ barbero.nombre }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Servicio
              </label>
              <select 
                v-model="servicioSeleccionado"
                class="w-full rounded px-3 py-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }"
              >
                <option value="">Todos</option>
                <option v-for="servicio in servicios" :key="servicio.id_servicio" :value="servicio.id_servicio">
                  {{ servicio.nombre }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Método de Pago
              </label>
              <select 
                v-model="metodoPagoSeleccionado"
                class="w-full rounded px-3 py-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }"
              >
                <option value="">Todos</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
                <option value="otro">Otro</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Estado Reserva
              </label>
              <select 
                v-model="estadoReservaSeleccionado"
                class="w-full rounded px-3 py-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }"
              >
                <option value="">Todos</option>
                <option value="pendiente_pago">Pendiente Pago</option>
                <option value="confirmada">Confirmada</option>
                <option value="en_proceso">En Proceso</option>
                <option value="completada">Completada</option>
                <option value="cancelada">Cancelada</option>
                <option value="no_asistio">No Asistió</option>
              </select>
            </div>
            <div class="flex items-end gap-2">
              <button 
                @click="aplicarFiltros"
                class="flex-1 px-4 py-2 rounded transition font-medium"
                :style="{ 
                  backgroundColor: 'var(--color-primary)',
                  color: 'var(--color-base)'
                }"
              >
                Aplicar
              </button>
              <button 
                @click="limpiarFiltros"
                class="flex-1 px-4 py-2 rounded transition font-medium"
                :style="{ 
                  backgroundColor: 'var(--color-neutral)',
                  color: 'var(--color-base)'
                }"
              >
                Limpiar
              </button>
            </div>
          </div>
        </div>

        <!-- Gráficos y Reportes -->
        <FullReportsChart 
          :pagos-data="pagosData" 
          :reservas-data="reservasData"
          :ingresos-mensuales="ingresosMensuales"
          :ranking-barberos="rankingBarberos"
          :servicios-mas-populares="serviciosMasPopulares"
          :clientes-frecuentes="clientesFrecuentes"
          :distribucion-estados="distribucionEstados"
          :distribucion-metodos-pago="distribucionMetodosPago"
        />
      </div>
    </div>
  </AppLayout>
</template>
