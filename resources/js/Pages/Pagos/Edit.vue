<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  pago: Object,
  reservas: Array,
  metodosPago: Array,
  tiposPago: Array,
  estadosPago: Array,
})

const form = useForm({
  id_reserva: props.pago.id_reserva ?? '',
  monto_total: props.pago.monto_total ?? 0,
  fecha_pago: props.pago.fecha_pago ?? '',
  metodo_pago: props.pago.metodo_pago ?? 'efectivo',
  tipo_pago: props.pago.tipo_pago ?? 'pago_completo',
  estado: props.pago.estado ?? 'pendiente',
  notas: props.pago.notas ?? '',
})

function submit() {
  form.put(route('pagos.update', props.pago.id_pago))
}
</script>

<template>
  <AppLayout :title="'Editar pago #' + (props.pago?.id_pago || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" :style="{ color: 'var(--color-neutral)' }">Editar pago</h2>
        <Link :href="route('pagos.index')" class="px-3 py-2 rounded transition" 
          :style="{ backgroundColor: 'var(--color-secondary)', color: 'var(--color-base)' }">
          Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-6 space-y-6" :style="{ backgroundColor: 'var(--color-base)' }">
          
          <!-- Información básica -->
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">Reserva</label>
              <select v-model="form.id_reserva" class="w-full rounded px-3 py-2 transition" 
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }">
                <option v-for="r in reservas" :key="r.id_reserva" :value="r.id_reserva">
                  #{{ r.id_reserva }} - {{ r.cliente?.user?.name }} / {{ r.barbero?.user?.name }}
                </option>
              </select>
              <div v-if="form.errors.id_reserva" class="text-sm mt-1" :style="{ color: 'var(--color-error)' }">
                {{ form.errors.id_reserva }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">Monto Total</label>
              <input v-model.number="form.monto_total" type="number" step="0.01" min="0" class="w-full rounded px-3 py-2 transition" 
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }" />
              <div v-if="form.errors.monto_total" class="text-sm mt-1" :style="{ color: 'var(--color-error)' }">
                {{ form.errors.monto_total }}
              </div>
            </div>
          </div>

          <!-- Fecha y métodos de pago -->
          <div class="grid md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">Fecha de Pago</label>
              <input v-model="form.fecha_pago" type="date" class="w-full rounded px-3 py-2 transition" 
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }" />
              <div v-if="form.errors.fecha_pago" class="text-sm mt-1" :style="{ color: 'var(--color-error)' }">
                {{ form.errors.fecha_pago }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">Método de Pago</label>
              <select v-model="form.metodo_pago" class="w-full rounded px-3 py-2 transition" 
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }">
                <option v-for="m in metodosPago" :key="m" :value="m">{{ m }}</option>
              </select>
              <div v-if="form.errors.metodo_pago" class="text-sm mt-1" :style="{ color: 'var(--color-error)' }">
                {{ form.errors.metodo_pago }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">Tipo de Pago</label>
              <select v-model="form.tipo_pago" class="w-full rounded px-3 py-2 transition" 
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }">
                <option v-for="t in tiposPago" :key="t" :value="t">{{ t }}</option>
              </select>
              <div v-if="form.errors.tipo_pago" class="text-sm mt-1" :style="{ color: 'var(--color-error)' }">
                {{ form.errors.tipo_pago }}
              </div>
            </div>
          </div>

          <!-- Estado -->
          <div>
            <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">Estado</label>
            <select v-model="form.estado" class="w-full rounded px-3 py-2 transition" 
              :style="{ 
                borderColor: 'var(--color-neutral)',
                borderWidth: '1px',
                backgroundColor: 'var(--color-base)',
                color: 'var(--color-neutral)'
              }">
              <option v-for="e in estadosPago" :key="e" :value="e">{{ e }}</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm mt-1" :style="{ color: 'var(--color-error)' }">
              {{ form.errors.estado }}
            </div>
          </div>

          <!-- Notas -->
          <div>
            <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">Notas</label>
            <textarea v-model="form.notas" class="w-full rounded px-3 py-2 transition" rows="4"
              :style="{ 
                borderColor: 'var(--color-neutral)',
                borderWidth: '1px',
                backgroundColor: 'var(--color-base)',
                color: 'var(--color-neutral)'
              }"></textarea>
            <div v-if="form.errors.notas" class="text-sm mt-1" :style="{ color: 'var(--color-error)' }">
              {{ form.errors.notas }}
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="flex gap-2">
            <button @click="submit" :disabled="form.processing" class="px-4 py-2 rounded transition font-medium"
              :style="{ 
                backgroundColor: form.processing ? 'var(--color-neutral)' : 'var(--color-primary)',
                color: 'var(--color-base)',
                opacity: form.processing ? 0.6 : 1
              }">
              {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
            </button>
            <Link :href="route('pagos.index')" class="px-4 py-2 rounded transition font-medium" 
              :style="{ backgroundColor: 'var(--color-secondary)', color: 'var(--color-base)' }">
              Cancelar
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
