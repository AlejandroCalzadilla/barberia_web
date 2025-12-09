<script setup>
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { useRoles } from '@/composables/useRoles.js'

const props = defineProps({
  servicios: Object,
  filters: Object,
})

const q = ref(props.filters?.q || '')
const { isPropietario } = useRoles()

function search() {
  router.get(route('servicios.index'), {  q: q.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar servicio?')) {
    router.delete(route('servicios.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Servicios">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Servicios</h2>
        <Link v-if="isPropietario" :href="route('servicios.create')" 
              class="px-3 py-2 text-white rounded hover:opacity-90 transition" 
              style="background-color: var(--color-primary);">
          Nuevo
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-4" style="background-color: var(--color-base); border: 2px solid var(--color-neutral)">
          <div class="flex gap-2 mb-4">
            <input 
              v-model="q" 
              @keyup.enter="search" 
              type="text" 
              placeholder="Buscar por nombre" 
              class="border rounded px-3 py-2 w-full" 
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
            />
            <button 
              @click="search" 
              class="px-3 py-2 text-white rounded hover:opacity-90 transition"
              style="background-color: var(--color-secondary);">
              Buscar
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm" style="background-color: var(--color-base);">
              <thead>
                <tr class="text-left border-b" style="border-color: var(--color-neutral);">
                  <th class="p-2" style="color: var(--color-neutral);">ID</th>
                  <th class="p-2" style="color: var(--color-neutral);">Nombre</th>
                  <th class="p-2" style="color: var(--color-neutral);">Duración</th>
                  <th class="p-2" style="color: var(--color-neutral);">Precio</th>
                  <th class="p-2" style="color: var(--color-neutral);">Estado</th>
                  <th class="p-2" style="color: var(--color-neutral);">Acciones</th>
                </tr>
              </thead>
              <tbody>
                
                <tr v-for="s in servicios.data" :key="s.id_servicio" class="border-b" style="border-color: var(--color-neutral);">
                  <td class="p-2" style="color: var(--color-neutral);">{{ s.id_servicio }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ s.nombre }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ s.duracion_minutos }} min</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ s.precio }}</td>
                  <td class="p-2">
                    <span class="px-2 py-1 rounded" style="background-color: var(--color-accent); color: var(--color-base); opacity: 0.8;">
                      {{ s.estado }}
                    </span>
                  </td>
                  <td class="p-2 flex gap-2">
                    <Link 
                      v-if="isPropietario" 
                      :href="route('servicios.edit', s.id_servicio)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-primary);">
                      Editar
                    </Link>
                    <button 
                      v-if="isPropietario" 
                      @click="destroyItem(s.id_servicio)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-error);">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="servicios.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link 
              v-for="l in servicios.links" 
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
