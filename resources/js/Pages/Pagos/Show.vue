<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  pago: Object,
})

function formatCurrency(amount) {
  return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB', minimumFractionDigits: 2 }).format(amount || 0)
}

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatDateTime(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

function getEstadoBadgeStyle(estado) {
  const styles = {
    'pendiente': { backgroundColor: 'var(--color-warning)', color: 'var(--color-base)' },
    'completado': { backgroundColor: 'var(--color-success)', color: 'var(--color-base)' },
    'rechazado': { backgroundColor: 'var(--color-error)', color: 'var(--color-base)' },
    'reembolsado': { backgroundColor: 'var(--color-info)', color: 'var(--color-base)' },
  }
  return styles[estado] || { backgroundColor: 'var(--color-neutral)', color: 'var(--color-base)' }
}
</script>

<template>
  <AppLayout :title="'Pago #' + (props.pago?.id_pago || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" :style="{ color: 'var(--color-neutral)' }">
          Pago #{{ props.pago?.id_pago }}
        </h2>
        <Link :href="route('pagos.index')" class="px-3 py-2 rounded transition" 
          :style="{ backgroundColor: 'var(--color-secondary)', color: 'var(--color-base)' }">
          Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Información del Pago -->
        <div class="shadow sm:rounded-lg p-6 mb-6" :style="{ backgroundColor: 'var(--color-base)' }">
          <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
            Información del Pago
          </h3>
          
          <div class="grid md:grid-cols-2 gap-6">
            <!-- Monto Total -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Monto Total
              </label>
              <p class="text-2xl font-bold" :style="{ color: 'var(--color-primary)' }">
                {{ formatCurrency(pago.monto_total) }}
              </p>
            </div>

            <!-- Estado -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Estado
              </label>
              <span class="px-3 py-1 rounded-full font-semibold text-sm" 
                :style="getEstadoBadgeStyle(pago.estado)">
                {{ pago.estado.charAt(0).toUpperCase() + pago.estado.slice(1) }}
              </span>
            </div>

            <!-- Fecha de Pago -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Fecha de Pago
              </label>
              <p :style="{ color: 'var(--color-neutral)' }">
                {{ formatDate(pago.fecha_pago) }}
              </p>
            </div>

            <!-- Método de Pago -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Método de Pago
              </label>
              <p :style="{ color: 'var(--color-neutral)' }">
                {{ pago.metodo_pago.charAt(0).toUpperCase() + pago.metodo_pago.slice(1) }}
              </p>
            </div>

            <!-- Tipo de Pago -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Tipo de Pago
              </label>
              <p :style="{ color: 'var(--color-neutral)' }">
                {{ pago.tipo_pago === 'pago_completo' ? 'Pago Completo' : pago.tipo_pago === 'pago_parcial' ? 'Pago Parcial' : 'Anticipo' }}
              </p>
            </div>

            <!-- ID del Pago -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                ID del Pago
              </label>
              <p class="font-mono text-sm" :style="{ color: 'var(--color-neutral)' }">
                {{ pago.id_pago }}
              </p>
            </div>
          </div>
        </div>

        <!-- Información de la Reserva -->
        <div class="shadow sm:rounded-lg p-6 mb-6" :style="{ backgroundColor: 'var(--color-base)' }">
          <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
            Información de la Reserva
          </h3>
          
          <div v-if="pago.reserva" class="grid md:grid-cols-2 gap-6">
            <!-- ID Reserva -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Reserva #
              </label>
              <p class="font-semibold" :style="{ color: 'var(--color-primary)' }">
                {{ pago.reserva.id_reserva }}
              </p>
            </div>

            <!-- Cliente -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Cliente
              </label>
              <p :style="{ color: 'var(--color-neutral)' }">
                {{ pago.reserva.cliente?.user?.name || 'N/A' }}
              </p>
            </div>

            <!-- Barbero -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Barbero
              </label>
              <p :style="{ color: 'var(--color-neutral)' }">
                {{ pago.reserva.barbero?.user?.name || 'N/A' }}
              </p>
            </div>

            <!-- Servicio -->
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Servicio
              </label>
              <p :style="{ color: 'var(--color-neutral)' }">
                {{ pago.reserva.servicio?.nombre || 'N/A' }}
              </p>
            </div>
          </div>
          <div v-else class="text-center p-4" :style="{ color: 'var(--color-error)' }">
            Sin información de reserva
          </div>
        </div>

        <!-- Notas -->
        <div v-if="pago.notas" class="shadow sm:rounded-lg p-6 mb-6" :style="{ backgroundColor: 'var(--color-base)' }">
          <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
            Notas
          </h3>
          <p class="whitespace-pre-wrap" :style="{ color: 'var(--color-neutral)' }">
            {{ pago.notas }}
          </p>
        </div>

        <!-- Auditoría -->
        <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
          <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
            Auditoría
          </h3>
          
          <div class="grid md:grid-cols-2 gap-6 text-sm">
            <div>
              <label class="block font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Creado
              </label>
              <p :style="{ color: 'var(--color-neutral)' }">
                {{ formatDateTime(pago.created_at) }}
              </p>
            </div>

            <div>
              <label class="block font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Actualizado
              </label>
              <p :style="{ color: 'var(--color-neutral)' }">
                {{ formatDateTime(pago.updated_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex gap-2 mt-6">
          <Link :href="route('pagos.edit', pago.id_pago)" class="px-4 py-2 rounded transition font-medium" 
            :style="{ backgroundColor: 'var(--color-primary)', color: 'var(--color-base)' }">
            Editar
          </Link>
          <Link :href="route('pagos.index')" class="px-4 py-2 rounded transition font-medium" 
            :style="{ backgroundColor: 'var(--color-secondary)', color: 'var(--color-base)' }">
            Volver al listado
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
