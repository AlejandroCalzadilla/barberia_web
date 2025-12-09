<script setup>
import { ref, computed, onMounted } from 'vue'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend } from 'chart.js'
import { Line, Bar } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend)

const props = defineProps({
  pagosData: Array,
  reservasData: Array,
})

const chartType = ref('pagos') // 'pagos' o 'reservas'

/* console.log('props.pagosData:', props.pagosData)
// Calcular datos para el gráfico de pagos
const pagosChartData = computed(() => {
  if (!props.pagosData || props.pagosData.length === 0) {
    return {
      labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
      datasets: [{
        label: 'Pagos Completados',
        data: [0, 0, 0, 0, 0, 0],
        borderColor: 'var(--color-primary, #3b82f6)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true,
      }]
    }
  }

  // Agrupar pagos por mes
  const monthlyData = {}
  props.pagosData.forEach(pago => {
    const date = new Date(pago.fecha_pago)
    const month = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
    if (!monthlyData[month]) {
      monthlyData[month] = 0
    }
    monthlyData[month] += pago.monto_total || 0
  })

  const labels = Object.keys(monthlyData).slice(-6)
  const data = labels.map(m => monthlyData[m])

  return {
    labels: labels.map(m => {
      const [year, month] = m.split('-')
      return new Date(year, month - 1).toLocaleDateString('es-CO', { month: 'short', year: '2-digit' })
    }),
    datasets: [{
      label: 'Pagos Completados',
      data: data,
      borderColor: 'var(--color-success, #10b981)',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4,
      fill: true,
    }]
  }
}) */

// Calcular datos para el gráfico de reservas por estado
const reservasChartData = computed(() => {
  if (!props.reservasData || props.reservasData.length === 0) {
    return {
      labels: ['Pendiente', 'Confirmada', 'Completada', 'Cancelada'],
      datasets: [{
        label: 'Reservas',
        data: [0, 0, 0, 0],
        backgroundColor: [
          'rgba(234, 179, 8, 0.8)',
          'rgba(59, 130, 246, 0.8)',
          'rgba(16, 185, 129, 0.8)',
          'rgba(239, 68, 68, 0.8)',
        ],
      }]
    }
  }

  // Contar reservas por estado
  const stateData = {
    'pendiente': 0,
    'confirmada': 0,
    'completada': 0,
    'cancelada': 0,
  }

  props.reservasData.forEach(res => {
    const estado = (res.estado || 'pendiente').toLowerCase()
    if (stateData.hasOwnProperty(estado)) {
      stateData[estado]++
    }
  })

  return {
    labels: ['Pendiente', 'Confirmada', 'Completada', 'Cancelada'],
    datasets: [{
      label: 'Reservas',
      data: [stateData.pendiente, stateData.confirmada, stateData.completada, stateData.cancelada],
      backgroundColor: [
        'rgba(234, 179, 8, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(239, 68, 68, 0.8)',
      ],
      borderColor: [
        'rgba(234, 179, 8, 1)',
        'rgba(59, 130, 246, 1)',
        'rgba(16, 185, 129, 1)',
        'rgba(239, 68, 68, 1)',
      ],
      borderWidth: 2,
    }]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      display: true,
      labels: {
        color: 'var(--color-neutral, #6b7280)',
        font: {
          size: 12,
        }
      }
    },
    tooltip: {
      backgroundColor: 'var(--color-neutral, #1f2937)',
      titleColor: 'var(--color-base, #ffffff)',
      bodyColor: 'var(--color-base, #ffffff)',
      borderColor: 'var(--color-primary, #3b82f6)',
      borderWidth: 1,
    }
  },
  scales: {
    y: {
      ticks: {
        color: 'var(--color-neutral, #6b7280)',
      },
      grid: {
        color: 'rgba(107, 114, 128, 0.1)',
      }
    },
    x: {
      ticks: {
        color: 'var(--color-neutral, #6b7280)',
      },
      grid: {
        color: 'rgba(107, 114, 128, 0.1)',
      }
    }
  }
}
</script>

<template>
  <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
    <div class="mb-4">
      <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
        Reportes y Estadísticas
      </h3>
      
      <div class="flex gap-2 mb-4">
        <button
          @click="chartType = 'pagos'"
          :style="{
            backgroundColor: chartType === 'pagos' ? 'var(--color-primary)' : 'var(--color-secondary)',
            color: 'var(--color-base)'
          }"
          class="px-4 py-2 rounded transition font-medium text-sm"
        >
          Pagos Mensuales
        </button>
        <button
          @click="chartType = 'reservas'"
          :style="{
            backgroundColor: chartType === 'reservas' ? 'var(--color-primary)' : 'var(--color-secondary)',
            color: 'var(--color-base)'
          }"
          class="px-4 py-2 rounded transition font-medium text-sm"
        >
          Reservas por Estado
        </button>
      </div>
    </div>

    <!-- Gráfico de Pagos -->
    <div v-if="chartType === 'pagos'" class="h-80">
      <Line :data="pagosChartData" :options="chartOptions" />
    </div>

    <!-- Gráfico de Reservas -->
    <div v-if="chartType === 'reservas'" class="h-80">
      <Bar :data="reservasChartData" :options="chartOptions" />
    </div>
  </div>
</template>
