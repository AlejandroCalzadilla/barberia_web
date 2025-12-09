<script setup>
import { ref, computed } from 'vue'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler } from 'chart.js'
import { Line, Bar, Pie, Doughnut } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler)

const props = defineProps({
  pagosData: Array,
  reservasData: Array,
  ingresosMensuales: Object,
  rankingBarberos: Array,
  serviciosMasPopulares: Array,
  clientesFrecuentes: Array,
  distribucionEstados: Object,
  distribucionMetodosPago: Array,
})

const chartType = ref('pagos')

// Gráfico de Pagos Mensuales
const pagosChartData = computed(() => {
  console.log('📊 FullReportsChart - pagosData:', props.pagosData)
  
  if (!props.pagosData || props.pagosData.length === 0) {
    console.warn('⚠️ No hay datos de pagos')
    return { 
      labels: ['Sin datos'], 
      datasets: [{
        label: 'Pagos Completados',
        data: [0],
        borderColor: 'var(--color-success, #10b981)',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true,
      }] 
    }
  }

  const monthlyData = {}
  props.pagosData.forEach(pago => {
    // Extraer solo la parte de la fecha (YYYY-MM-DD) del string ISO
    const fechaStr = pago.fecha_pago.split('T')[0] // "2025-11-02"
    const [year, month, day] = fechaStr.split('-')
    const monthKey = `${year}-${month}`
    
    if (!monthlyData[monthKey]) {
      monthlyData[monthKey] = 0
    }
    monthlyData[monthKey] += parseFloat(pago.monto_total) || 0
  })

  console.log('📅 Datos mensuales agrupados:', monthlyData)

  const sortedMonths = Object.keys(monthlyData).sort()
  const labels = sortedMonths.slice(-6)
  const data = labels.map(m => monthlyData[m])

  console.log('📈 Labels finales:', labels)
  console.log('💰 Data final:', data)
  console.log('📊 Verificación - Hay datos?', data.length > 0, 'Total:', data.reduce((a,b) => a+b, 0))

  if (data.length === 0 || data.every(v => v === 0)) {
    console.warn('⚠️ Todos los datos son 0 o no hay datos')
  }

  const monthNames = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic']
  const formattedLabels = labels.map(m => {
    const [year, month] = m.split('-')
    return `${monthNames[parseInt(month) - 1]} '${year.slice(-2)}`
  })

  console.log('🏷️ Labels formateados:', formattedLabels)

  const chartData = {
    labels: formattedLabels,
    datasets: [{
      label: 'Pagos Completados (Bs)',
      data: data,
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4,
      fill: true,
    }]
  }
  
  console.log('📊 ChartData completo:', chartData)
  
  return chartData
})

// Gráfico de Reservas por Estado
const reservasChartData = computed(() => {
  console.log('📊 reservasData:', props.reservasData)
  
  if (!props.reservasData || props.reservasData.length === 0) {
    console.warn('⚠️ No hay datos de reservas')
    return { labels: [], datasets: [] }
  }

  const stateData = {
    'pendiente_pago': 0,
    'confirmada': 0,
    'en_proceso': 0,
    'completada': 0,
    'cancelada': 0,
    'no_asistio': 0,
  }

  props.reservasData.forEach(res => {
    const estado = (res.estado || 'pendiente_pago').toLowerCase()
    if (stateData.hasOwnProperty(estado)) {
      stateData[estado]++
    }
  })
  
  console.log('📊 Estados agrupados:', stateData)

  const chartData = {
    labels: ['Pendiente Pago', 'Confirmada', 'En Proceso', 'Completada', 'Cancelada', 'No Asistió'],
    datasets: [{
      label: 'Reservas',
      data: [
        stateData.pendiente_pago, 
        stateData.confirmada, 
        stateData.en_proceso, 
        stateData.completada, 
        stateData.cancelada, 
        stateData.no_asistio
      ],
      backgroundColor: [
        'rgba(234, 179, 8, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(156, 163, 175, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(239, 68, 68, 0.8)',
        'rgba(249, 115, 22, 0.8)',
      ],
      borderColor: [
        'rgba(234, 179, 8, 1)',
        'rgba(59, 130, 246, 1)',
        'rgba(156, 163, 175, 1)',
        'rgba(16, 185, 129, 1)',
        'rgba(239, 68, 68, 1)',
        'rgba(249, 115, 22, 1)',
      ],
      borderWidth: 2,
    }]
  }
  
  console.log('📊 reservasChartData:', chartData)
  return chartData
})

// Gráfico de Barberos Top
const barberosChartData = computed(() => {
  if (!props.rankingBarberos || props.rankingBarberos.length === 0) {
    return { labels: [], datasets: [] }
  }

  const topBarberos = props.rankingBarberos.slice(0, 5)
  
  return {
    labels: topBarberos.map(b => b.nombre),
    datasets: [{
      label: 'Ingresos Generados',
      data: topBarberos.map(b => b.ingresos_generados),
      backgroundColor: 'rgba(59, 130, 246, 0.8)',
      borderColor: 'rgba(59, 130, 246, 1)',
      borderWidth: 1,
    }]
  }
})

// Gráfico de Servicios Populares
const serviciosChartData = computed(() => {
  if (!props.serviciosMasPopulares || props.serviciosMasPopulares.length === 0) {
    return { labels: [], datasets: [] }
  }

  return {
    labels: props.serviciosMasPopulares.map(s => s.nombre),
    datasets: [{
      label: 'Veces Completado',
      data: props.serviciosMasPopulares.map(s => s.veces_completado),
      backgroundColor: 'rgba(16, 185, 129, 0.8)',
      borderColor: 'rgba(16, 185, 129, 1)',
      borderWidth: 1,
    }]
  }
})

// Gráfico de Distribución de Métodos de Pago (Pie)
const metodosPagoChartData = computed(() => {
  if (!props.distribucionMetodosPago || props.distribucionMetodosPago.length === 0) {
    return { labels: [], datasets: [] }
  }

  const colors = [
    'rgba(59, 130, 246, 0.8)',
    'rgba(16, 185, 129, 0.8)',
    'rgba(234, 179, 8, 0.8)',
    'rgba(239, 68, 68, 0.8)',
    'rgba(139, 92, 246, 0.8)',
  ]

  return {
    labels: props.distribucionMetodosPago.map(m => m.metodo_pago),
    datasets: [{
      label: 'Cantidad de Pagos',
      data: props.distribucionMetodosPago.map(m => m.cantidad_pagos),
      backgroundColor: colors.slice(0, props.distribucionMetodosPago.length),
      borderColor: colors.slice(0, props.distribucionMetodosPago.length),
      borderWidth: 2,
    }]
  }
})

// Gráfico de Distribución de Estados (Doughnut)
const estadosChartData = computed(() => {
  if (!props.distribucionEstados) {
    return { labels: [], datasets: [] }
  }

  const data = props.distribucionEstados
  
  return {
    labels: ['Completadas', 'Canceladas', 'No Asistió', 'Confirmadas', 'En Proceso', 'Pendiente Pago'],
    datasets: [{
      label: 'Reservas',
      data: [
        data.completadas || 0, 
        data.canceladas || 0, 
        data.no_asistio || 0, 
        data.confirmadas || 0,
        data.en_proceso || 0,
        data.pendiente_pago || 0
      ],
      backgroundColor: [
        'rgba(16, 185, 129, 0.8)',
        'rgba(239, 68, 68, 0.8)',
        'rgba(249, 115, 22, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(156, 163, 175, 0.8)',
        'rgba(234, 179, 8, 0.8)',
      ],
      borderColor: [
        'rgba(16, 185, 129, 1)',
        'rgba(239, 68, 68, 1)',
        'rgba(249, 115, 22, 1)',
        'rgba(59, 130, 246, 1)',
        'rgba(156, 163, 175, 1)',
        'rgba(234, 179, 8, 1)',
      ],
      borderWidth: 2,
    }]
  }
})

const getTextColor = () => {
  return getComputedStyle(document.documentElement).getPropertyValue('--color-neutral').trim() || '#6b7280'
}

const chartOptions = computed(() => {
  const textColor = getTextColor()
  const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#3b82f6'
  
  console.log('🎨 Chart options - textColor:', textColor, 'primaryColor:', primaryColor)
  
  return {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        display: true,
        labels: {
          color: textColor,
          font: { size: 12 }
        }
      },
      title: {
        color: textColor,
        font: { size: 14, weight: 'bold' }
      },
      tooltip: {
        backgroundColor: 'rgba(31, 41, 55, 0.95)',
        titleColor: '#ffffff',
        bodyColor: '#ffffff',
        borderColor: primaryColor,
        borderWidth: 1,
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { color: textColor },
        grid: { color: 'rgba(107, 114, 128, 0.1)' }
      },
      x: {
        ticks: { color: textColor },
        grid: { color: 'rgba(107, 114, 128, 0.1)' }
      }
    }
  }
})

const pieOptions = computed(() => {
  const textColor = getTextColor()
  const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#3b82f6'
  
  return {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        display: true,
        position: 'bottom',
        labels: {
          color: textColor,
          font: { size: 12 }
        }
      },
      title: {
        color: textColor,
        font: { size: 14, weight: 'bold' }
      },
      tooltip: {
        backgroundColor: 'rgba(31, 41, 55, 0.95)',
        titleColor: '#ffffff',
        bodyColor: '#ffffff',
        borderColor: primaryColor,
        borderWidth: 1,
      }
    }
  }
})
</script>

<template>
  <div class="space-y-6">
    <!-- Gráficos principales -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Pagos Mensuales -->
      <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
        <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
          Pagos Mensuales
        </h3>
        <div class="h-80">
          <Line :data="pagosChartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Reservas por Estado -->
      <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
        <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
          Distribución de Reservas
        </h3>
        <div class="h-80" >
          <Bar :data="reservasChartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Barberos Top 5 -->
      <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
        <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
          Top 5 Barberos
        </h3>
        <div class="h-80">
          <Bar :data="barberosChartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Servicios Populares -->
      <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
        <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
          Servicios Más Populares
        </h3>
        <div class="h-80">
          <Bar :data="serviciosChartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Métodos de Pago -->
      <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
        <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
          Métodos de Pago
        </h3>
        <div class="h-80">
          <Pie :data="metodosPagoChartData" :options="pieOptions" />
        </div>
      </div>

      <!-- Distribución de Estados -->
      <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
        <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
          Estados de Reservas
        </h3>
        <div class="h-80">
          <Doughnut :data="estadosChartData" :options="pieOptions" />
        </div>
      </div>
    </div>

    <!-- Tabla de Barberos -->
    <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
      <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
        Ranking de Barberos
      </h3>
      <div v-if="rankingBarberos && rankingBarberos.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y" :style="{ borderColor: 'var(--color-neutral)' }">
          <thead :style="{ backgroundColor: 'var(--color-secondary)', color: 'var(--color-base)' }">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Barbero</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Especialidad</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Servicios</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Completados</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Ingresos</th>
            </tr>
          </thead>
          <tbody class="divide-y" :style="{ borderColor: 'var(--color-neutral)' }">
            <tr v-for="barbero in rankingBarberos" :key="barbero.id_barbero" class="hover:opacity-75">
              <td class="px-6 py-4 text-sm" :style="{ color: 'var(--color-neutral)' }">{{ barbero.nombre }}</td>
              <td class="px-6 py-4 text-sm" :style="{ color: 'var(--color-neutral)' }">{{ barbero.especialidad }}</td>
              <td class="px-6 py-4 text-sm" :style="{ color: 'var(--color-neutral)' }">{{ barbero.total_servicios }}</td>
              <td class="px-6 py-4 text-sm font-medium" :style="{ color: 'var(--color-primary)' }">{{ barbero.servicios_completados }}</td>
              <td class="px-6 py-4 text-sm font-medium" :style="{ color: 'var(--color-success)' }">Bs {{ barbero.ingresos_generados.toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else :style="{ color: 'var(--color-neutral)' }">Sin datos disponibles</div>
    </div>

    <!-- Tabla de Clientes Frecuentes -->
    <div class="shadow sm:rounded-lg p-6" :style="{ backgroundColor: 'var(--color-base)' }">
      <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
        Clientes Frecuentes
      </h3>
      <div v-if="clientesFrecuentes && clientesFrecuentes.length > 0" class="overflow-x-auto">
        <table class="min-w-full divide-y" :style="{ borderColor: 'var(--color-neutral)' }">
          <thead :style="{ backgroundColor: 'var(--color-secondary)', color: 'var(--color-base)' }">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Cliente</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Total Reservas</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Gasto Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase">Última Visita</th>
            </tr>
          </thead>
          <tbody class="divide-y" :style="{ borderColor: 'var(--color-neutral)' }">
            <tr v-for="cliente in clientesFrecuentes" :key="cliente.id_cliente" class="hover:opacity-75">
              <td class="px-6 py-4 text-sm" :style="{ color: 'var(--color-neutral)' }">{{ cliente.nombre }}</td>
              <td class="px-6 py-4 text-sm" :style="{ color: 'var(--color-neutral)' }">{{ cliente.email }}</td>
              <td class="px-6 py-4 text-sm" :style="{ color: 'var(--color-neutral)' }">{{ cliente.total_reservas }}</td>
              <td class="px-6 py-4 text-sm font-medium" :style="{ color: 'var(--color-success)' }">Bs {{ cliente.gasto_total.toFixed(2) }}</td>
              <td class="px-6 py-4 text-sm" :style="{ color: 'var(--color-neutral)' }">{{ new Date(cliente.ultima_visita).toLocaleDateString('es-BO', { year: 'numeric', month: 'short', day: 'numeric' }) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else :style="{ color: 'var(--color-neutral)' }">Sin datos disponibles</div>
    </div>
  </div>
</template>
