<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  position: {
    type: String,
    default: 'bottom-right', // 'bottom-right', 'bottom-left', 'top-right', 'top-left'
    validator: (value) => ['bottom-right', 'bottom-left', 'top-right', 'top-left'].includes(value)
  },
  showIcon: {
    type: Boolean,
    default: true
  }
})

const page = usePage()
const currentViews = ref(0)
const isLoading = ref(true)
const isVisible = ref(false)
let updateInterval = null

const positionClasses = {
  'bottom-right': 'bottom-4 right-4',
  'bottom-left': 'bottom-4 left-4',
  'top-right': 'top-4 right-4',
  'top-left': 'top-4 left-4'
}

async function fetchViews() {
  try {
    const currentPath = window.location.pathname.replace(/^\//, '')
    console.log('🔍 PageViewCounter - Fetching views for path:', currentPath)
    console.log('🔍 PageViewCounter - Full URL:', window.location.href)
    console.log('🔍 PageViewCounter - Route exists?', typeof route === 'function')
    
    const url = route('page-views.current')
    console.log('🔍 PageViewCounter - API URL:', url)
    
    const response = await axios.get(url, {
      params: { path: currentPath }
    })
    
    console.log('✅ PageViewCounter - Response:', response.data)
    
    if (response.data.success) {
      currentViews.value = response.data.views
      isLoading.value = false
      
      console.log('📊 PageViewCounter - Views updated:', currentViews.value)
      
      // Mostrar el contador con animación
      setTimeout(() => {
        isVisible.value = true
      }, 300)
    } else {
      console.warn('⚠️ PageViewCounter - Success=false in response')
    }
  } catch (error) {
    console.error('❌ PageViewCounter - Error al obtener visitas:', error)
    console.error('❌ PageViewCounter - Error details:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    })
    isLoading.value = false
  }
}

function formatViews(views) {
  if (views >= 1000000) {
    return (views / 1000000).toFixed(1) + 'M'
  } else if (views >= 1000) {
    return (views / 1000).toFixed(1) + 'K'
  }
  return views.toString()
}

onMounted(() => {
  fetchViews()
  
  // Actualizar cada 30 segundos
  updateInterval = setInterval(() => {
    fetchViews()
  }, 30000)
})

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval)
  }
})
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="translate-y-4 opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-4 opacity-0"
  >
    <div
      v-if="isVisible && !isLoading"
      :class="['fixed z-40 flex items-center gap-2 px-3 py-2 rounded-lg shadow-lg backdrop-blur-sm transition-all hover:scale-105', positionClasses[position]]"
      :style="{
        backgroundColor: 'rgba(var(--color-neutral-rgb, 0, 0, 0), 0.8)',
        border: '1px solid rgba(var(--color-primary-rgb, 255, 255, 255), 0.2)'
      }"
    >
      <!-- Icono de ojo -->
      <span v-if="showIcon" class="text-lg opacity-70">👁️</span>
      
      <!-- Contador -->
      <div class="flex flex-col items-end">
        <span 
          class="text-sm font-bold tabular-nums"
          style="color: var(--color-primary, #fff)"
        >
          {{ formatViews(currentViews) }}
        </span>
        <span 
          class="text-xs opacity-60"
          style="color: var(--color-base, #fff)"
        >
          {{ currentViews === 1 ? 'visita' : 'visitas' }}
        </span>
      </div>
      
      <!-- Indicador de actualización -->
      <div 
        class="absolute -top-1 -right-1 w-2 h-2 rounded-full animate-pulse"
        style="background-color: var(--color-success, #10b981)"
      ></div>
    </div>
  </Transition>
</template>

<style scoped>
/* Animación de pulso suave */
@keyframes pulse-soft {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
