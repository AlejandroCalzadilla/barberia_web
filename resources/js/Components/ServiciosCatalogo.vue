<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import ServicioModal from '@/Components/ServicioModal.vue'

const page = usePage()
const servicioSeleccionado = ref(null)
const mostrarModal = ref(false)
const busqueda = ref('')

// Los servicios vienen desde las props de la página
const servicios = computed(() => page.props.servicios || [])

const serviciosFiltrados = computed(() => {
  return servicios.value.filter(s => 
    s.nombre.toLowerCase().includes(busqueda.value.toLowerCase())
  )
})

function abrirModal(servicio) {
  servicioSeleccionado.value = servicio
  mostrarModal.value = true
}

function cerrarModal() {
  mostrarModal.value = false
  servicioSeleccionado.value = null
}
</script>

<template>
  <div class="space-y-6">
    <!-- Encabezado -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg p-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Nuestros Servicios</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        Explora nuestros servicios y reserva tu cita hoy
      </p>
      
      <!-- Búsqueda -->
      <input 
        v-model="busqueda" 
        type="text" 
        placeholder="Buscar servicios..." 
        class="w-full px-4 py-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
      />
    </div>

    <!-- Catálogo de servicios -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="servicio in serviciosFiltrados" 
        :key="servicio.id_servicio"
        class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg hover:shadow-lg transition-shadow cursor-pointer"
        @click="abrirModal(servicio)"
      >
        <!-- Imagen del servicio -->
        <div v-if="servicio.imagen" class="w-full h-48 bg-gray-200 dark:bg-gray-700 overflow-hidden">
          <img 
            :src="servicio.imagen.startsWith('http') ? servicio.imagen : `https://mail.tecnoweb.org.bo/inf513/grupo11sa/proyecto2/public${servicio.imagen}`" 
            :alt="servicio.nombre"
            class="w-full h-full object-cover"
           >
        </div>
        <div v-else class="w-full h-48 bg-gradient-to-br from-indigo-100 to-indigo-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
          <span class="text-gray-400 dark:text-gray-500 text-4xl">📷</span>
        </div>

        <!-- Encabezado -->
        <div class="p-6">
          <div class="flex items-start justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ servicio.nombre }}
            </h3>
          </div>

          <!-- Descripción -->
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
            {{ servicio.descripcion || 'Sin descripción disponible' }}
          </p>

          <!-- Precio y duración -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="p-3 rounded" style="background-color: var(--color-primary); ">
              <p class="text-xs " style="color: var(--color-neutral);">Precio</p>
              <p class="text-lg font-bold" style="color: var(--color-neutral);">
                BOB{{ servicio.precio }}
              </p>
            </div>
            <div class="p-3 rounded" style="background-color: var(--color-primary); ">
              <p class="text-xs ">Duración</p>
              <p class="text-lg font-bold" style="color: var(--color-neutral);">
                {{ servicio.duracion_minutos }} min
              </p>
            </div>
          </div>

          <!-- Botón Ver detalles -->
          <button 
            class="w-full py-2 px-4 rounded font-medium transition-colors text-white"
            style="background-color: var(--color-primary);"
            @click.stop="abrirModal(servicio)"
          >
            Ver detalles
          </button>
        </div>
      </div>

      <!-- Sin resultados -->
      <div v-if="serviciosFiltrados.length === 0" class="col-span-full text-center py-12">
        <p class="text-gray-600 dark:text-gray-400 text-lg">
          No se encontraron servicios
        </p>
      </div>
    </div>

    <!-- Modal con detalles -->
    <ServicioModal 
      v-if="mostrarModal && servicioSeleccionado"
      :servicio="servicioSeleccionado"
      @close="cerrarModal"
    />
  </div>
</template>
