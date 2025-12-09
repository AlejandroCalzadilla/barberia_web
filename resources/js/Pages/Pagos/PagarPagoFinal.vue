<script setup>
import { ref, onUnmounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  pago: Object,
  servicio: Object,
  barbero: Object,
  fecha_reserva: String,
  hora_inicio: String,
  hora_fin: String,
  monto_total: Number,
  id_pago: Number,
  id_reserva: Number,
})

const qrGenerado = ref(false)
const qrImage = ref(null)
const transactionId = ref(null)
const nroPago = ref(null)
const cargandoQR = ref(false)
const errorQR = ref(null)
const verificandoPago = ref(false)
const estadoPago = ref(null)
const mensajeEstado = ref('')
let intervalVerificacion = null

function formatoPrecio(precio) {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'BOB',
  }).format(precio)
}

function formatoFecha(fecha) {
  return new Date(fecha).toLocaleDateString('es-ES', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

async function generarQR() {
  cargandoQR.value = true
  errorQR.value = null
  estadoPago.value = null

  try {
    const response = await axios.post(route('pagofacil.generar-qr'), {
      id_pago: props.id_pago,
      id_reserva: props.id_reserva,
      metodo_pago: 'qr',
      es_pago_final: true,
    })

    if (response.data.success) {
      qrImage.value = response.data.qr_image
      transactionId.value = response.data.transaction_id
      nroPago.value = response.data.nro_pago
      qrGenerado.value = true
      estadoPago.value = 'pendiente'
      mensajeEstado.value = 'Esperando confirmación del pago...'
      
      iniciarVerificacion()
    } else {
      errorQR.value = response.data.message || 'Error al generar el código QR'
    }
  } catch (error) {
    console.error('Error al generar QR:', error)
    errorQR.value = error.response?.data?.message || 'Error de conexión al generar el QR'
  } finally {
    cargandoQR.value = false
  }
}

async function verificarEstadoPago() {
  if (!props.id_pago) return
  
  verificandoPago.value = true
  
  try {
    const response = await axios.get(`/api/pagos/${props.id_pago}/estado`)
    
    if (response.data.success) {
      const estado = response.data.pago.estado
      
      if (estado === 'pagado') {
        estadoPago.value = 'pagado'
        mensajeEstado.value = '¡Pago confirmado exitosamente! ✅'
        detenerVerificacion()
        
        setTimeout(() => {
          router.get(route('pagos.index'))
        }, 3000)
      } else if (estado === 'cancelado') {
        estadoPago.value = 'error'
        mensajeEstado.value = 'El pago fue cancelado ❌'
        detenerVerificacion()
      } else {
        estadoPago.value = 'pendiente'
        mensajeEstado.value = 'Esperando confirmación del pago...'
      }
    }
  } catch (error) {
    console.error('Error al verificar estado:', error)
  } finally {
    verificandoPago.value = false
  }
}

function iniciarVerificacion() {
  verificarEstadoPago()
  intervalVerificacion = setInterval(() => {
    verificarEstadoPago()
  }, 5000)
}

function detenerVerificacion() {
  if (intervalVerificacion) {
    clearInterval(intervalVerificacion)
    intervalVerificacion = null
  }
}

function volverPagos() {
  detenerVerificacion()
  router.get(route('pagos.index'))
}

onUnmounted(() => {
  detenerVerificacion()
})
</script>

<template>
  <AppLayout title="Completar Pago">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">
          Completar Pago Final
        </h2>
        <Link 
          :href="route('pagos.index')" 
          class="px-4 py-2 rounded hover:opacity-90 transition"
          style="background-color: var(--color-secondary); color: var(--color-base);">
          ← Volver a mis pagos
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-6">
          
          <!-- Resumen de la reserva y pago -->
          <div class="shadow sm:rounded-lg p-6" style="background-color: var(--color-base);">
            <h3 class="text-lg font-semibold mb-4" style="color: var(--color-neutral);">
              Resumen del pago
            </h3>

            <div class="space-y-4">
              <!-- Servicio -->
              <div class="border-b pb-3" style="border-color: var(--color-neutral); ">
                <p class="text-sm mb-1" style="color: var(--color-neutral); ">Servicio</p>
                <p class="font-semibold" style="color: var(--color-neutral);">{{ servicio.nombre }}</p>
                <p class="text-sm" style="color: var(--color-neutral); opacity: 0.7;">{{ servicio.duracion_minutos }} minutos</p>
              </div>

              <!-- Barbero -->
              <div class="border-b pb-3" style="border-color: var(--color-neutral); ">
                <p class="text-sm mb-1" style="color: var(--color-neutral); ">Barbero</p>
                <p class="font-semibold" style="color: var(--color-neutral);">{{ barbero.user?.name }}</p>
              </div>

              <!-- Fecha y hora -->
              <div class="border-b pb-3" style="border-color: var(--color-neutral); ">
                <p class="text-sm mb-1" style="color: var(--color-neutral); ">Fecha</p>
                <p class="font-semibold" style="color: var(--color-neutral);">{{ formatoFecha(fecha_reserva) }}</p>
              </div>

              <div class="border-b pb-3" style="border-color: var(--color-neutral); ">
                <p class="text-sm mb-1" style="color: var(--color-neutral); ">Horario</p>
                <p class="font-semibold" style="color: var(--color-neutral);">{{ hora_inicio }} - {{ hora_fin }}</p>
              </div>

              <!-- Info del pago -->
              <div class="border-b pb-3" style="border-color: var(--color-neutral); ">
                <p class="text-sm mb-1" style="color: var(--color-neutral); ">Tipo de pago</p>
                <p class="font-semibold px-2 py-1 rounded inline-block" style="background-color: var(--color-warning); color: var(--color-base);">
                  Pago Final (50%)
                </p>
              </div>

              <!-- Total -->
              <div class="pt-3">
                <p class="text-sm mb-1" style="color: var(--color-neutral); ">Total a pagar</p>
                <p class="text-2xl font-bold" style="color: var(--color-primary);">
                  {{ formatoPrecio(monto_total) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Área de pago por QR -->
          <div class="shadow sm:rounded-lg p-6" style="background-color: var(--color-base);">
            <h3 class="text-lg font-semibold mb-4" style="color: var(--color-neutral);">
              Pago por QR
            </h3>

            <div class="text-center space-y-4">
              
              <!-- Área del QR -->
              <div 
                class="border-2 border-dashed rounded-lg p-8 min-h-[300px] flex items-center justify-center"
                :style="{ borderColor: 'var(--color-neutral)',  }">
                
                <!-- Estado: No generado -->
                <div v-if="!qrGenerado && !cargandoQR" class="text-center">
                  <div class="text-6xl mb-4">📱</div>
                  <p class="mb-4" style="color: var(--color-neutral);">
                    Haz clic en el botón para generar el código QR de pago
                  </p>
                  <button 
                    @click="generarQR"
                    class="px-6 py-3 rounded text-white font-medium hover:opacity-90 transition"
                    style="background-color: var(--color-primary);">
                    Generar QR
                  </button>
                  
                  <div v-if="errorQR" class="mt-4 p-3 rounded text-sm" 
                    style="background-color: rgba(239, 68, 68, 0.1); color: #ef4444;">
                    {{ errorQR }}
                  </div>
                </div>

                <!-- Estado: Cargando -->
                <div v-else-if="cargandoQR" class="text-center">
                  <div class="animate-spin text-6xl mb-4">⏳</div>
                  <p style="color: var(--color-neutral);">
                    Generando código QR...
                  </p>
                </div>

                <!-- Estado: QR Generado -->
                <div v-else class="text-center">
                  <div 
                    v-if="estadoPago" 
                    class="mb-4 p-4 rounded-lg font-semibold text-lg"
                    :class="{
                      'bg-yellow-100 text-yellow-800': estadoPago === 'pendiente',
                      'bg-green-100 text-green-800': estadoPago === 'pagado',
                      'bg-red-100 text-red-800': estadoPago === 'error'
                    }">
                    <div class="flex items-center justify-center gap-2">
                      <span v-if="estadoPago === 'pendiente'" class="animate-pulse">⏳</span>
                      <span v-if="estadoPago === 'pagado'">✅</span>
                      <span v-if="estadoPago === 'error'">❌</span>
                      {{ mensajeEstado }}
                    </div>
                  </div>

                  <div class="w-64 h-64 rounded-lg flex items-center justify-center mx-auto mb-4 bg-white p-4"
                    :class="{ 'opacity-50': estadoPago === 'pagado' }">
                    <img 
                      v-if="qrImage" 
                      :src="qrImage" 
                      alt="Código QR de pago"
                      class="w-full h-full object-contain"
                    />
                  </div>
                  
                  <p class="text-sm mb-2" style="color: var(--color-neutral);">
                    Escanea el código con tu aplicación bancaria
                  </p>
                  
                  <div class="text-xs space-y-1" style="color: var(--color-neutral);">
                    <p><strong>Monto:</strong> {{ formatoPrecio(monto_total) }}</p>
                    <p><strong>Nro. Pago:</strong> {{ nroPago }}</p>
                    <p v-if="estadoPago === 'pendiente'" class="text-blue-600 font-semibold mt-2">
                      💡 Verificando estado del pago automáticamente...
                    </p>
                    <p v-else-if="estadoPago === 'pagado'" class="text-green-600 font-semibold mt-2">
                      🎉 ¡Pago completado! Redirigiendo...
                    </p>
                  </div>
                </div>
              </div>

              <!-- Instrucciones -->
              <div class="text-left p-4 rounded" style="background-color: var(--color-primary); ">
                <p class="text-sm font-semibold mb-2" style="color: var(--color-neutral);">Instrucciones:</p>
                <ol class="text-sm space-y-1 list-decimal list-inside" style="color: var(--color-neutral);">
                  <li>Genera el código QR</li>
                  <li>Abre tu aplicación bancaria</li>
                  <li>Escanea el código QR</li>
                  <li>Confirma el pago de {{ formatoPrecio(monto_total) }}</li>
                  <li>Tu pago se confirmará automáticamente</li>
                </ol>
              </div>

              <!-- Botones de acción -->
              <div class="flex gap-3 pt-4">
                <button 
                  @click="volverPagos"
                  class="flex-1 px-4 py-3 rounded border text-center hover:opacity-80 transition"
                  :style="{ 
                    borderColor: 'var(--color-neutral)', 
                    color: 'var(--color-neutral)' 
                  }">
                  {{ qrGenerado ? 'He completado el pago' : 'Cancelar' }}
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>
