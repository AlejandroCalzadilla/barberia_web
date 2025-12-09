<script setup>
import { computed } from 'vue'

const props = defineProps({
  horarios: Array,
  barberoNombre: String,
})

const diasOrdenados = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']

const diasDetalles = computed(() => {
  if (!props.horarios) return []
  return diasOrdenados.map(dia => {
    const horario = props.horarios.find(h => h.dia_semana === dia)
    return {
      dia,
      horario,
      diaNombre: dia.charAt(0).toUpperCase() + dia.slice(1),
    }
  })
})

const totalHoras = computed(() => {
  if (!props.horarios || props.horarios.length === 0) return 0
  return props.horarios.reduce((total, h) => {
    const duracion = calculateDuration(h.hora_inicio, h.hora_fin)
    return total + parseFloat(duracion)
  }, 0).toFixed(1)
})

function calculateDuration(inicio, fin) {
  const [hI, mI] = inicio.split(':').map(Number)
  const [hF, mF] = fin.split(':').map(Number)
  const totalMinutos = (hF * 60 + mF) - (hI * 60 + mI)
  return (totalMinutos / 60).toFixed(1)
}

function getEstadoColor(estado) {
  return estado === 'activo' 
    ? 'var(--color-success)' 
    : 'var(--color-error)'
}
</script>

<template>
  <div class="space-y-4">
    <!-- Encabezado -->
    <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
      <h2 class="text-2xl font-bold" :style="{ color: 'var(--color-neutral)' }">
        Horario de {{ barberoNombre }}
      </h2>
      <p class="text-sm mt-2" :style="{ color: 'var(--color-neutral)', opacity: 0.7 }">
        Tu calendario laboral semanal
      </p>
    </div>

    <!-- Grid de horarios -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div 
        v-for="item in diasDetalles" 
        :key="item.dia"
        class="shadow sm:rounded-lg p-6 transition-all hover:shadow-lg"
        :style="{ 
          backgroundColor: 'var(--color-base)',
          borderLeft: `4px solid ${item.horario ? getEstadoColor(item.horario.estado) : 'var(--color-neutral)'}`,
        }"
      >
        <!-- Nombre del día -->
        <h3 class="text-lg font-semibold mb-3" :style="{ color: 'var(--color-neutral)' }">
          {{ item.diaNombre }}
        </h3>

        <!-- Contenido -->
        <div v-if="item.horario" class="space-y-2">
          <!-- Horario -->
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium" :style="{ color: 'var(--color-primary)' }">
              🕐
            </span>
            <div>
              <p class="text-sm" :style="{ color: 'var(--color-neutral)' }">
                <strong>{{ item.horario.hora_inicio }}</strong> - <strong>{{ item.horario.hora_fin }}</strong>
              </p>
              <p class="text-xs mt-1" :style="{ color: 'var(--color-neutral)', opacity: 0.7 }">
                Duración: {{ calculateDuration(item.horario.hora_inicio, item.horario.hora_fin) }} horas
              </p>
            </div>
          </div>

          <!-- Estado -->
          <div class="flex items-center gap-2 mt-4">
            <span 
              class="px-2 py-1 rounded text-xs font-medium"
              :style="{ 
                backgroundColor: getEstadoColor(item.horario.estado),
                color: 'var(--color-base)',
              }"
            >
              {{ item.horario.estado === 'activo' ? '✓ Activo' : '✗ Inactivo' }}
            </span>
          </div>
        </div>

        <!-- Sin horario -->
        <div v-else class="text-center py-4">
          <p class="text-sm" :style="{ color: 'var(--color-neutral)', opacity: 0.5 }">
            Sin horario asignado
          </p>
        </div>
      </div>
    </div>

    <!-- Resumen -->
    <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
      <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
        Resumen Semanal
      </h3>
      <div class="grid grid-cols-3 gap-4">
        <div class="p-4 rounded" :style="{ backgroundColor: 'var(--color-secondary)' }">
          <p class="text-sm" :style="{ color: 'var(--color-base)' }">Días Asignados</p>
          <p class="text-2xl font-bold mt-1" :style="{ color: 'var(--color-base)' }">
            {{ diasDetalles.filter(d => d.horario).length }}
          </p>
        </div>
        <div class="p-4 rounded" :style="{ backgroundColor: 'var(--color-success)' }">
          <p class="text-sm" :style="{ color: 'var(--color-base)' }">Días Activos</p>
          <p class="text-2xl font-bold mt-1" :style="{ color: 'var(--color-base)' }">
            {{ diasDetalles.filter(d => d.horario?.estado === 'activo').length }}
          </p>
        </div>
        <div class="p-4 rounded" :style="{ backgroundColor: 'var(--color-error)' }">
          <p class="text-sm" :style="{ color: 'var(--color-base)' }">Horas Totales</p>
          <p class="text-2xl font-bold mt-1" :style="{ color: 'var(--color-base)' }">
            {{ totalHoras }}h
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
