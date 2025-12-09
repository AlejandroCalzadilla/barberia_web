<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const stats = ref([])
const isLoading = ref(true)
const totalPages = ref(0)
const totalViews = ref(0)
const sortBy = ref('views') // 'views' o 'path'
const sortOrder = ref('desc') // 'asc' o 'desc'
const searchQuery = ref('')

async function fetchStats() {
  isLoading.value = true
  try {
    const response = await axios.get('/api/page-views/stats')
    if (response.data.success) {
      stats.value = response.data.stats
      totalPages.value = response.data.total_pages
      totalViews.value = response.data.total_views
    }
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
  } finally {
    isLoading.value = false
  }
}

const filteredStats = computed(() => {
  let filtered = stats.value

  // Filtrar por búsqueda
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(stat => 
      stat.path.toLowerCase().includes(query)
    )
  }

  // Ordenar
  filtered.sort((a, b) => {
    const aVal = sortBy.value === 'views' ? a.views : a.path
    const bVal = sortBy.value === 'views' ? b.views : b.path
    
    if (sortOrder.value === 'asc') {
      return aVal > bVal ? 1 : -1
    } else {
      return aVal < bVal ? 1 : -1
    }
  })

  return filtered
})

function toggleSort(column) {
  if (sortBy.value === column) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = column
    sortOrder.value = 'desc'
  }
}

function formatPath(path) {
  // Convertir rutas a nombres más legibles
  const pathMap = {
    'dashboard': 'Dashboard',
    'servicios/catalogo': 'Catálogo de Servicios',
    'reservas': 'Reservas',
    'pagos': 'Pagos',
    'horarios': 'Horarios',
    'servicios': 'Servicios',
    'barberos': 'Barberos',
    'clientes': 'Clientes',
    'categorias': 'Categorías',
    'productos': 'Productos',
    'reportes': 'Reportes',
    'usuarios': 'Usuarios',
  }

  return pathMap[path] || path
}

function getPathColor(index) {
  const colors = [
    'var(--color-primary)',
    'var(--color-secondary)',
    'var(--color-success)',
    'var(--color-warning)',
    'var(--color-danger)',
  ]
  return colors[index % colors.length]
}

onMounted(() => {
  fetchStats()
})
</script>

<template>
  <AppLayout title="Estadísticas de Visitas">
    <template #header>
      <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">
        📊 Estadísticas de Visitas
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Tarjetas de resumen -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="rounded-lg shadow p-6" style="background-color: var(--color-base);">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-70" style="color: var(--color-neutral);">Total de Páginas</p>
                <p class="text-3xl font-bold mt-1" style="color: var(--color-primary);">{{ totalPages }}</p>
              </div>
              <div class="text-4xl opacity-20">📄</div>
            </div>
          </div>

          <div class="rounded-lg shadow p-6" style="background-color: var(--color-base);">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-70" style="color: var(--color-neutral);">Total de Visitas</p>
                <p class="text-3xl font-bold mt-1" style="color: var(--color-success);">{{ totalViews.toLocaleString() }}</p>
              </div>
              <div class="text-4xl opacity-20">👁️</div>
            </div>
          </div>

          <div class="rounded-lg shadow p-6" style="background-color: var(--color-base);">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-70" style="color: var(--color-neutral);">Promedio por Página</p>
                <p class="text-3xl font-bold mt-1" style="color: var(--color-warning);">
                  {{ totalPages > 0 ? Math.round(totalViews / totalPages) : 0 }}
                </p>
              </div>
              <div class="text-4xl opacity-20">📈</div>
            </div>
          </div>
        </div>

        <!-- Barra de búsqueda y acciones -->
        <div class="rounded-lg shadow p-4 mb-6 flex items-center justify-between gap-4" 
          style="background-color: var(--color-base);">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="🔍 Buscar página..."
            class="flex-1 px-4 py-2 rounded border"
            :style="{
              backgroundColor: 'var(--color-base-light)',
              borderColor: 'var(--color-neutral)',
              color: 'var(--color-neutral)'
            }"
          />
          <button
            @click="fetchStats"
            class="px-4 py-2 rounded text-white hover:opacity-90 transition"
            style="background-color: var(--color-primary);"
          >
            🔄 Actualizar
          </button>
        </div>

        <!-- Tabla de estadísticas -->
        <div class="rounded-lg shadow overflow-hidden" style="background-color: var(--color-base);">
          <div v-if="isLoading" class="text-center py-12">
            <div class="animate-spin text-6xl">⏳</div>
            <p class="mt-4" style="color: var(--color-neutral);">Cargando estadísticas...</p>
          </div>

          <div v-else-if="filteredStats.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">📭</div>
            <p style="color: var(--color-neutral);">No hay estadísticas disponibles</p>
          </div>

          <table v-else class="min-w-full divide-y" :style="{ borderColor: 'var(--color-neutral)' }">
            <thead style="background-color: var(--color-base-dark);">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" 
                  style="color: var(--color-neutral);">
                  #
                </th>
                <th 
                  @click="toggleSort('path')"
                  class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer hover:opacity-70" 
                  style="color: var(--color-neutral);">
                  Página
                  <span v-if="sortBy === 'path'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                </th>
                <th 
                  @click="toggleSort('views')"
                  class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider cursor-pointer hover:opacity-70" 
                  style="color: var(--color-neutral);">
                  Visitas
                  <span v-if="sortBy === 'views'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" 
                  style="color: var(--color-neutral);">
                  Porcentaje
                </th>
              </tr>
            </thead>
            <tbody class="divide-y" :style="{ borderColor: 'var(--color-neutral)' }">
              <tr v-for="(stat, index) in filteredStats" :key="stat.path" 
                class="hover:opacity-80 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium" :style="{ color: getPathColor(index) }">
                    {{ index + 1 }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium" style="color: var(--color-neutral);">
                      {{ formatPath(stat.path) }}
                    </span>
                    <span class="text-xs opacity-50" style="color: var(--color-neutral);">
                      /{{ stat.path }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-bold" style="color: var(--color-primary);">
                      {{ stat.views.toLocaleString() }}
                    </span>
                    <div class="w-32 h-2 rounded-full overflow-hidden" 
                      style="background-color: var(--color-base-dark);">
                      <div 
                        class="h-full transition-all"
                        :style="{ 
                          width: `${(stat.views / Math.max(...filteredStats.map(s => s.views))) * 100}%`,
                          backgroundColor: getPathColor(index)
                        }"
                      ></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm" style="color: var(--color-neutral);">
                    {{ ((stat.views / totalViews) * 100).toFixed(1) }}%
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb), 0.1);
}
</style>
