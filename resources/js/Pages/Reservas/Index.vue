<script setup>
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ReservasCalendario from '@/Components/ReservasCalendario.vue'
import { ref } from 'vue'
import { useRoles } from '@/composables/useRoles.js'

const props = defineProps({
  reservas: Object,
  clientes: Array,
  barberos: Array,
  servicios: Array,
  filters: Object,
  isBarbero: Boolean,
  isCliente: Boolean,
  barberoNombre: String,
  clienteNombre: String,
})

const cliente = ref(props.filters?.cliente || '')
const barbero = ref(props.filters?.barbero || '')
const estado = ref(props.filters?.estado || '')
const fecha = ref(props.filters?.fecha || '')
const { isPropietario, isBarbero: isUserBarbero, isCliente: isUserCliente } = useRoles()

function search() {
  router.get(route('reservas.index'), { cliente: cliente.value, barbero: barbero.value, estado: estado.value, fecha: fecha.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar reserva?')) {
    router.delete(route('reservas.destroy', id))
  }
}
</script>

<template>
  <AppLayout :title="isBarbero ? 'Mis Reservas' : isCliente ? 'Mis Reservas' : 'Reservas'">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">
          {{ isBarbero ? 'Mis Reservas' : isCliente ? 'Mis Reservas' : 'Reservas' }}
        </h2>
        <Link v-if="isPropietario && !isBarbero" :href="route('reservas.create')" 
              class="px-3 py-2 text-white rounded hover:opacity-90 transition" 
              style="background-color: var(--color-primary);">
          Nueva
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Vista para barberos y clientes - Calendario -->
        <ReservasCalendario 
          v-if="isBarbero || isCliente" 
          :reservas="reservas"
          :barbero-nombre="barberoNombre"
          :cliente-nombre="clienteNombre"
        />

        <!-- Vista para administradores - Tabla -->
        <div v-else class="shadow sm:rounded-lg p-4" style="background-color: var(--color-base); border: 2px solid var(--color-neutral)">
          <div class="grid md:grid-cols-4 gap-2 mb-4">
            <select 
              v-model="cliente" 
              class="border rounded px-3 py-2"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);">
              <option value="">Todos los clientes</option>
              <option v-for="c in clientes" :key="c.id_cliente" :value="c.id_cliente">{{ c.user?.name }}</option>
            </select>
            <select 
              v-model="barbero" 
              class="border rounded px-3 py-2"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);">
              <option value="">Todos los barberos</option>
              <option v-for="b in barberos" :key="b.id_barbero" :value="b.id_barbero">{{ b.user?.name }}</option>
            </select>
            <select 
              v-model="estado" 
              class="border rounded px-3 py-2"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);">
              <option value="">Todos los estados</option>
              <option value="pendiente_pago">Pendiente de pago</option>
              <option value="confirmada">Confirmada</option>
              <option value="en_proceso">En proceso</option>
              <option value="completada">Completada</option>
              <option value="cancelada">Cancelada</option>
              <option value="no_asistio">No asistió</option>
            </select>
            <div class="flex gap-2">
              <input 
                v-model="fecha" 
                type="date" 
                class="border rounded px-3 py-2 w-full" 
                style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
              />
              <button 
                @click="search" 
                class="px-3 py-2 text-white rounded hover:opacity-90 transition"
                style="background-color: var(--color-secondary);">
                Filtrar
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm" style="background-color: var(--color-base);">
              <thead>
                <tr class="text-left border-b" style="border-color: var(--color-neutral);">
                  <th class="p-2" style="color: var(--color-neutral);">ID</th>
                  <th class="p-2" style="color: var(--color-neutral);">Cliente</th>
                  <th class="p-2" style="color: var(--color-neutral);">Barbero</th>
                  <th class="p-2" style="color: var(--color-neutral);">Servicio</th>
                  <th class="p-2" style="color: var(--color-neutral);">Fecha</th>
                  <th class="p-2" style="color: var(--color-neutral);">Horario</th>
                  <th class="p-2" style="color: var(--color-neutral);">Estado</th>
                  <th class="p-2" style="color: var(--color-neutral);">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in reservas.data" :key="r.id_reserva" class="border-b" style="border-color: var(--color-neutral);">
                  <td class="p-2" style="color: var(--color-neutral);">{{ r.id_reserva }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ r.cliente?.user?.name }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ r.barbero?.user?.name }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ r.servicio?.nombre }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ r.fecha_reserva }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ r.hora_inicio }} - {{ r.hora_fin }}</td>
                  <td class="p-2">
                    <span class="px-2 py-1 rounded" style="background-color: var(--color-accent); color: var(--color-base); opacity: 0.8;">
                      {{ r.estado }}
                    </span>
                  </td>
                  <td class="p-2 flex gap-2">
                    <Link 
                      v-if="isPropietario || isUserBarbero" 
                      :href="route('reservas.edit', r.id_reserva)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-primary);">
                      Editar
                    </Link>
                    <button 
                      v-if="isPropietario" 
                      @click="destroyItem(r.id_reserva)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-error);">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="reservas.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link 
              v-for="l in reservas.links" 
              :key="l.url + l.label" 
              :href="l.url || '#'" 
              preserve-state 
              replace
              class="px-3 py-1 rounded border transition hover:opacity-80"
              :style="l.active 
                ? 'background-color: var(--color-primary); color: var(--color-base); border-color: var(--color-primary);' 
                : 'background-color: var(--color-base); color: var(--color-neutral); border-color: var(--color-neutral);'"
              v-html="l.label" 
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
