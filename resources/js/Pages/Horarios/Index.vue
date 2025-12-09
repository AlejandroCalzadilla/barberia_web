<script setup>
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import HorarioVisual from '@/Components/HorarioVisual.vue'
import { ref, computed } from 'vue'
import { useRoles } from '@/composables/useRoles.js'

const props = defineProps({
  horarios: [Object, Array],
  barberos: Array,
  filters: Object,
  isBarbero: Boolean,
})

const barbero = ref(props.filters?.barbero || '')
const { isPropietario, isBarbero: isUserBarbero, user } = useRoles()

const barberoNombre = computed(() => {
  if (props.isBarbero) {
    return user.value?.name || 'Mi'
  }
  return ''
})

function search() {
  router.get(route('horarios.index'), { barbero: barbero.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar horario?')) {
    router.delete(route('horarios.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Horarios">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">
          {{ isBarbero ? 'Mi Horario' : 'Horarios' }}
        </h2>
        <Link v-if="isPropietario && !isBarbero" :href="route('horarios.create')" 
              class="px-3 py-2 text-white rounded hover:opacity-90 transition" 
              style="background-color: var(--color-primary);">
          Nuevo
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Vista para barberos - Horario Visual -->
        <HorarioVisual 
          v-if="isBarbero" 
          :horarios="horarios"
          :barbero-nombre="barberoNombre"
        />

        <!-- Vista para administradores - Tabla -->
        <div v-else class="shadow sm:rounded-lg p-4" style="background-color: var(--color-base); border: 2px solid var(--color-neutral)">
          <div class="flex gap-2 mb-4">
            <select 
              v-model="barbero" 
              class="border rounded px-3 py-2"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);">
              <option value="">Todos los barberos</option>
              <option v-for="b in barberos" :key="b.id_barbero" :value="b.id_barbero">{{ b.user?.name }}</option>
            </select>
            <button 
              @click="search" 
              class="px-3 py-2 text-white rounded hover:opacity-90 transition"
              style="background-color: var(--color-secondary);">
              Filtrar
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm" style="background-color: var(--color-base);">
              <thead>
                <tr class="text-left border-b" style="border-color: var(--color-neutral);">
                  <th class="p-2" style="color: var(--color-neutral);">ID</th>
                  <th class="p-2" style="color: var(--color-neutral);">Barbero</th>
                  <th class="p-2" style="color: var(--color-neutral);">Día</th>
                  <th class="p-2" style="color: var(--color-neutral);">Inicio</th>
                  <th class="p-2" style="color: var(--color-neutral);">Fin</th>
                  <th class="p-2" style="color: var(--color-neutral);">Estado</th>
                  <th class="p-2" style="color: var(--color-neutral);">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="h in horarios.data" :key="h.id_horario" class="border-b" style="border-color: var(--color-neutral);">
                  <td class="p-2" style="color: var(--color-neutral);">{{ h.id_horario }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ h.barbero?.user?.name }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ h.dia_semana }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ h.hora_inicio }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ h.hora_fin }}</td>
                  <td class="p-2">
                    <span class="px-2 py-1 rounded" style="background-color: var(--color-accent); color: var(--color-base); opacity: 0.8;">
                      {{ h.estado }}
                    </span>
                  </td>
                  <td class="p-2 flex gap-2">
                    <Link 
                      v-if="isPropietario" 
                      :href="route('horarios.edit', h.id_horario)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-primary);">
                      Editar
                    </Link>
                    <button 
                      v-if="isPropietario" 
                      @click="destroyItem(h.id_horario)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-error);">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="horarios.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link 
              v-for="l in horarios.links" 
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
