<script setup>
import { computed } from 'vue'

const props = defineProps({
  reservas: Array,
  barberoNombre: String,
  clienteNombre: String,
})

// Determinar si es vista de cliente o barbero
const esVistaCliente = computed(() => !!props.clienteNombre)

// Agrupar reservas por fecha
const reservasPorFecha = computed(() => {
  const agrupadas = {}
  
  if (!props.reservas || props.reservas.length === 0) {
    return agrupadas
  }

  props.reservas.forEach(reserva => {
    if (!agrupadas[reserva.fecha_reserva]) {
      agrupadas[reserva.fecha_reserva] = []
    }
    agrupadas[reserva.fecha_reserva].push(reserva)
  })

  return agrupadas
})

// Obtener fechas ordenadas
const fechasOrdenadas = computed(() => {
  return Object.keys(reservasPorFecha.value).sort().slice(0, 30) // Próximas 30 fechas
})

function getEstadoColor(estado) {
  const colores = {
    'pendiente_pago': 'var(--color-warning)',
    'confirmada': 'var(--color-success)',
    'en_proceso': 'var(--color-primary)',
    'completada': 'var(--color-secondary)',
    'cancelada': 'var(--color-error)',
    'no_asistio': 'var(--color-error)',
  }
  return colores[estado] || 'var(--color-neutral)'
}

function getNombreFecha(fecha) {
  const date = new Date(fecha + 'T00:00:00')
  const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
  const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
  return `${dias[date.getDay()]} ${date.getDate()} ${meses[date.getMonth()]}`
}

function formatoHora(hora) {
  return hora.substring(0, 5)
}
</script>

<template>
  <div class="space-y-4">
    <!-- Encabezado -->
    <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
      <h2 class="text-2xl font-bold" :style="{ color: 'var(--color-neutral)' }">
        Mis Reservas
      </h2>
      <p class="text-sm mt-2" :style="{ color: 'var(--color-neutral)', opacity: 0.7 }">
        Próximas citas agendadas
      </p>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-3 gap-4">
      <div class="shadow sm:rounded-lg p-4" :style="{ backgroundColor: 'var(--color-base)', borderLeft: '4px solid var(--color-primary)' }">
        <p class="text-sm" :style="{ color: 'var(--color-neutral)' }">Total Reservas</p>
        <p class="text-2xl font-bold mt-1" :style="{ color: 'var(--color-primary)' }">
          {{ reservas?.length || 0 }}
        </p>
      </div>
      
      <div class="shadow sm:rounded-lg p-4" :style="{ backgroundColor: 'var(--color-base)', borderLeft: '4px solid var(--color-success)' }">
        <p class="text-sm" :style="{ color: 'var(--color-neutral)' }">Confirmadas</p>
        <p class="text-2xl font-bold mt-1" :style="{ color: 'var(--color-success)' }">
          {{ reservas?.filter(r => r.estado === 'confirmada').length || 0 }}
        </p>
      </div>

      <div class="shadow sm:rounded-lg p-4" :style="{ backgroundColor: 'var(--color-base)', borderLeft: '4px solid var(--color-warning)' }">
        <p class="text-sm" :style="{ color: 'var(--color-neutral)' }">Pendiente de Pago</p>
        <p class="text-2xl font-bold mt-1" :style="{ color: 'var(--color-warning)' }">
          {{ reservas?.filter(r => r.estado === 'pendiente_pago').length || 0 }}
        </p>
      </div>
    </div>

    <!-- Lista de reservas por fecha -->
    <div class="space-y-3">
      <div 
        v-for="fecha in fechasOrdenadas" 
        :key="fecha"
        class="shadow sm:rounded-lg p-4" 
        :style="{ backgroundColor: 'var(--color-base)' }">
        
        <!-- Fecha -->
        <h3 class="font-bold text-lg mb-3" :style="{ color: 'var(--color-neutral)' }">
          {{ getNombreFecha(fecha) }}
        </h3>

        <!-- Reservas del día -->
        <div class="space-y-2">
          <div 
            v-for="reserva in reservasPorFecha[fecha]" 
            :key="reserva.id_reserva"
            class="p-3 rounded border-l-4 flex justify-between items-start"
            :style="{ 
              backgroundColor: 'rgba(0,0,0,0.05)',
              borderLeftColor: getEstadoColor(reserva.estado),
            }">
            
            <div class="flex-1">
              <!-- Hora y Servicio -->
              <div class="flex items-center gap-2 mb-1">
                <span class="font-bold" :style="{ color: 'var(--color-primary)' }">
                  {{ formatoHora(reserva.hora_inicio) }} - {{ formatoHora(reserva.hora_fin) }}
                </span>
                <span class="text-sm font-medium" :style="{ color: 'var(--color-neutral)' }">
                  {{ reserva.servicio.nombre }}
                </span>
              </div>

              <!-- Cliente o Barbero según la vista -->
              <p class="text-sm" :style="{ color: 'var(--color-neutral)' }">
                <template v-if="esVistaCliente">
                  Barbero: <strong>{{ reserva.barbero?.user?.name || 'No asignado' }}</strong>
                </template>
                <template v-else>
                  Cliente: <strong>{{ reserva.cliente?.user?.name || 'No asignado' }}</strong>
                </template>
              </p>

              <!-- Notas si existen -->
              <p v-if="reserva.notas" class="text-xs mt-1" :style="{ color: 'var(--color-neutral)', opacity: 0.7 }">
                📝 {{ reserva.notas }}
              </p>
            </div>

            <!-- Estado -->
            <span 
              class="px-3 py-1 rounded text-xs font-medium ml-2 whitespace-nowrap"
              :style="{ 
                backgroundColor: getEstadoColor(reserva.estado),
                color: 'var(--color-base)',
                opacity: 0.8,
              }">
              {{ reserva.estado }}
            </span>
          </div>
        </div>
      </div>

      <!-- Mensaje si no hay reservas -->
      <div v-if="fechasOrdenadas.length === 0" class="text-center py-8">
        <p :style="{ color: 'var(--color-neutral)', opacity: 0.7 }">
          No hay reservas agendadas en este momento
        </p>
      </div>
    </div>
  </div>
</template>
