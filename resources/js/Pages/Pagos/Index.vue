<script setup>
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { useRoles } from '@/composables/useRoles.js'

const props = defineProps({
  pagos: Object,
  reservas: Array,
  filters: Object,
  metodosPago: {
    type: Array,
    default: () => ['efectivo', 'tarjeta_credito', 'tarjeta_debito', 'transferencia', 'nequi', 'daviplata', 'otro']
  },
  tiposPago: {
    type: Array,
    default: () => ['anticipo', 'pago_parcial', 'pago_completo', 'producto']
  },
  estadosPago: {
    type: Array,
    default: () => ['pendiente', 'pagado', 'cancelado', 'reembolsado']
  },
})

const reserva = ref(props.filters?.reserva || '')
const estado = ref(props.filters?.estado || '')
const tipo = ref(props.filters?.tipo || '')
const fecha = ref(props.filters?.fecha || '')
const searchQuery = ref(props.filters?.search || '')

const { isPropietario, isBarbero, isCliente } = useRoles()

const filteredReservas = computed(() => {
  if (!searchQuery.value) return props.reservas.slice(0, 5)
  return props.reservas
    .filter(r => 
      r.id_reserva.toString().includes(searchQuery.value) ||
      r.cliente?.user?.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      r.barbero?.user?.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
    .slice(0, 5)
})

function search() {
  router.get(route('pagos.index'), { 
    reserva: reserva.value, 
    estado: estado.value, 
    tipo: tipo.value,
    fecha: fecha.value,
    search: searchQuery.value
  }, { preserveState: true, replace: true })
}

function resetFilters() {
  reserva.value = ''
  estado.value = ''
  tipo.value = ''
  fecha.value = ''
  searchQuery.value = ''
  search()
}

function destroyItem(id) {
  if (confirm('¿Está seguro de eliminar este pago? Esta acción no se puede deshacer.')) {
    router.delete(route('pagos.destroy', id), {
      onSuccess: () => {
        // Mostrar notificación de éxito
      },
      onError: (errors) => {
        alert('No se pudo eliminar el pago: ' + (errors.message || 'Error desconocido'))
      }
    })
  }
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 2 }).format(amount || 0)
}

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getEstadoBadgeStyle(estado) {
  const styles = {
    'pendiente': { backgroundColor: 'var(--color-warning)', color: 'var(--color-base)' },
    'pagado': { backgroundColor: 'var(--color-success)', color: 'var(--color-base)' },
    'cancelado': { backgroundColor: 'var(--color-error)', color: 'var(--color-base)' },
    'reembolsado': { backgroundColor: 'var(--color-info)', color: 'var(--color-base)' },
  }
  return styles[estado] || { backgroundColor: 'var(--color-neutral)', color: 'var(--color-base)' }
}

function getTipoPagoStyle(tipo) {
  const styles = {
    'anticipo': { backgroundColor: 'var(--color-primary)', color: 'var(--color-base)' },
    'pago_parcial': { backgroundColor: 'var(--color-secondary)', color: 'var(--color-base)' },
    'pago_completo': { backgroundColor: 'var(--color-success)', color: 'var(--color-base)' },
    'producto': { backgroundColor: 'var(--color-accent)', color: 'var(--color-base)' },
  }
  return styles[tipo] || { backgroundColor: 'var(--color-neutral)', color: 'var(--color-base)' }
}

function getTipoPagoLabel(tipo) {
  console.log('tipo pago:', tipo)
  const tipos = {
    'anticipo': 'Anticipo',
    'pago_parcial': 'Pago Parcial',
    'pago_completo': 'Pago Completo',
    'producto': 'Producto'
  }
  return tipos[tipo] || tipo
}
</script>

<template>
  <AppLayout title="Pagos">
    <template #header>
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold" :style="{ color: 'var(--color-neutral)' }">Gestión de Pagos</h2>
          <p class="mt-1 text-sm" :style="{ color: 'var(--color-neutral)' }">Administre los pagos de reservas</p>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filtros -->
        <div class="shadow rounded-lg p-4 mb-6" :style="{ backgroundColor: 'var(--color-base)' }">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="space-y-1">
              <label class="block text-sm font-medium" :style="{ color: 'var(--color-neutral)' }">Buscar reserva</label>
              <div class="relative">
                <input 
                  v-model="searchQuery" 
                  @keyup.enter="search" 
                  type="text" 
                  placeholder="ID o nombre de cliente" 
                  class="w-full pl-3 pr-10 py-2 rounded-md focus:outline-none focus:ring-2 transition"
                  :style="{ 
                    borderColor: 'var(--color-neutral)',
                    borderWidth: '1px',
                    backgroundColor: 'var(--color-base)',
                    color: 'var(--color-neutral)',
                    '--tw-ring-color': 'var(--color-primary)'
                  }"
                >
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" :style="{ color: 'var(--color-neutral)' }">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
              <div v-if="searchQuery && filteredReservas.length > 0" class="absolute z-10 mt-1 w-full shadow-lg rounded-md py-1 max-h-60 overflow-auto" :style="{ backgroundColor: 'var(--color-base)' }">
                <div v-for="r in filteredReservas" :key="r.id_reserva" 
                     @click="reserva = r.id_reserva; searchQuery = `#${r.id_reserva} - ${r.cliente?.user?.name}`"
                     class="px-4 py-2 text-sm cursor-pointer transition"
                     :style="{ color: 'var(--color-neutral)' }"
                     @mouseenter="$event.target.style.backgroundColor = 'var(--color-primary)'; $event.target.style.color = 'var(--color-base)'"
                     @mouseleave="$event.target.style.backgroundColor = 'transparent'; $event.target.style.color = 'var(--color-neutral)'">
                  #{{ r.id_reserva }} - {{ r.cliente?.user?.name }}
                </div>
              </div>
            </div>
            
            <div class="space-y-1">
              <label class="block text-sm font-medium" :style="{ color: 'var(--color-neutral)' }">Tipo de pago</label>
              <select v-model="tipo" class="w-full rounded-md py-2 px-3 focus:outline-none focus:ring-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)',
                  '--tw-ring-color': 'var(--color-primary)'
                }">
                <option value="">Todos los tipos</option>
                <option v-for="t in tiposPago" :key="t" :value="t">{{ getTipoPagoLabel(t) }}</option>
              </select>
            </div>
            
            <div class="space-y-1">
              <label class="block text-sm font-medium" :style="{ color: 'var(--color-neutral)' }">Estado</label>
              <select v-model="estado" class="w-full rounded-md py-2 px-3 focus:outline-none focus:ring-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)',
                  '--tw-ring-color': 'var(--color-primary)'
                }">
                <option value="">Todos los estados</option>
                <option v-for="e in estadosPago" :key="e" :value="e">{{ e.charAt(0).toUpperCase() + e.slice(1) }}</option>
              </select>
            </div>
            
            <div class="space-y-1">
              <label class="block text-sm font-medium" :style="{ color: 'var(--color-neutral)' }">Fecha</label>
              <input 
                v-model="fecha" 
                type="date" 
                class="w-full rounded-md py-2 px-3 focus:outline-none focus:ring-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)',
                  '--tw-ring-color': 'var(--color-primary)'
                }"
              >
            </div>
            
            <div class="flex items-end space-x-2">
              <button 
                @click="search" 
                class="w-full md:w-auto px-4 py-2 text-white rounded-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 transition font-medium"
                :style="{ 
                  backgroundColor: 'var(--color-primary)',
                  '--tw-ring-color': 'var(--color-primary)'
                }"
              >
                Filtrar
              </button>
              <button 
                @click="resetFilters" 
                class="w-full md:w-auto px-4 py-2 rounded-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 transition font-medium"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-secondary)',
                  color: 'var(--color-base)',
                  '--tw-ring-color': 'var(--color-secondary)'
                }"
              >
                Limpiar
              </button>
            </div>
          </div>
        </div>



        <!-- Tabla de pagos -->
        <div class="shadow overflow-hidden sm:rounded-lg" :style="{ backgroundColor: 'var(--color-base)' }">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y" :style="{ borderColor: 'var(--color-neutral)' }">
              <thead :style="{ backgroundColor: 'var(--color-accent)', borderColor: 'var(--color-neutral)' }">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--color-base)' }">Reserva</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--color-base)' }">Fecha</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--color-base)' }">Método</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--color-base)' }">Tipo</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--color-base)' }">Total</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--color-base)' }">Estado</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" :style="{ color: 'var(--color-base)' }">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y" :style="{ borderColor: 'var(--color-neutral)' }">
                <tr v-for="p in pagos.data" :key="p.id_pago" class="hover:opacity-75 transition" :style="{ backgroundColor: 'var(--color-base)' }">
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium" :style="{ color: 'var(--color-neutral)' }">
                      <span v-if="p.reserva">
                        #{{ p.id_reserva }} - {{ p.reserva.cliente?.user?.name || 'Cliente' }}
                      </span>
                      <span v-else :style="{ color: 'var(--color-error)' }">Sin reserva</span>
                    </div>
                    <div v-if="p.reserva" class="text-xs" :style="{ color: 'var(--color-neutral)', opacity: 0.7 }">
                      {{ p.reserva.servicio?.nombre || 'Sin servicio' }} - {{ p.reserva.barbero?.user?.name || 'Sin barbero' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: 'var(--color-neutral)' }">
                    {{ formatDate(p.fecha_pago) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm" :style="{ color: 'var(--color-neutral)' }">
                    {{ p.metodo_pago }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                          :style="getTipoPagoStyle(p.tipo_pago)">
                      {{ getTipoPagoLabel(p.tipo_pago) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :style="{ color: 'var(--color-primary)' }">
                    {{ formatCurrency(p.monto_total) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                          :style="getEstadoBadgeStyle(p.estado)">
                      {{ p.estado.charAt(0).toUpperCase() + p.estado.slice(1) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                      <!-- Botón Pagar para clientes con pagos pendientes tipo pago_final -->
                      <Link 
                        v-if="isCliente && p.estado === 'pendiente' && p.tipo_pago === 'pago_final' && p.reserva" 
                        :href="route('pagos.pagar-reserva', { 
                          id_pago: p.id_pago,
                          id_reserva: p.id_reserva
                        })" 
                        class="px-3 py-1 rounded text-white hover:opacity-90 transition font-medium"
                        title="Pagar ahora"
                        :style="{ backgroundColor: 'var(--color-success)' }"
                      >
                        💳 Pagar
                      </Link>
                      
                      <Link 
                        v-if="isPropietario " 
                        :href="route('pagos.edit', p.id_pago)" 
                        class="hover:opacity-75 transition"
                        title="Editar"
                        :style="{ color: 'var(--color-primary)' }"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </Link>
                      <button 
                        v-if="isPropietario" 
                        @click="destroyItem(p.id_pago)" 
                        class="hover:opacity-75 transition"
                        title="Eliminar"
                        :style="{ color: 'var(--color-error)' }"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                      <a 
                        :href="route('pagos.show', p.id_pago)" 
                        class="hover:opacity-75 transition"
                        title="Ver detalles"
                        :style="{ color: 'var(--color-secondary)' }"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </a>
                    </div>
                  </td>
                </tr>
                <tr v-if="pagos.data.length === 0">
                  <td colspan="7" class="px-6 py-4 text-center text-sm" :style="{ color: 'var(--color-neutral)' }">
                    No se encontraron pagos con los filtros seleccionados
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Paginación -->
          <div v-if="pagos.links?.length > 3" class="px-4 py-3 flex items-center justify-between border-t" :style="{ backgroundColor: 'var(--color-base)', borderColor: 'var(--color-neutral)' }">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm" :style="{ color: 'var(--color-neutral)' }">
                  Mostrando
                  <span class="font-medium">{{ pagos.from || 0 }}</span>
                  a
                  <span class="font-medium">{{ pagos.to || 0 }}</span>
                  de
                  <span class="font-medium">{{ pagos.total || 0 }}</span>
                  resultados
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <template v-for="(link, index) in pagos.links" :key="index">
                    <Link 
                      v-if="link.url"
                      :href="link.url" 
                      v-html="link.label"
                      class="relative inline-flex items-center px-4 py-2 text-sm font-medium transition"
                      :class="{
                        'rounded-l-md': index === 0,
                        'rounded-r-md': index === pagos.links.length - 1
                      }"
                      :style="{
                        backgroundColor: link.active ? 'var(--color-primary)' : 'var(--color-base)',
                        color: link.active ? 'var(--color-base)' : 'var(--color-neutral)',
                        borderColor: 'var(--color-neutral)',
                        borderWidth: '1px'
                      }"
                    />
                    <span 
                      v-else
                      v-html="link.label"
                      class="relative inline-flex items-center px-4 py-2 text-sm font-medium"
                      :style="{
                        backgroundColor: 'var(--color-base)',
                        color: 'var(--color-neutral)',
                        borderColor: 'var(--color-neutral)',
                        borderWidth: '1px'
                      }"
                    />
                  </template>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
