<script setup>
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  usuarios: Object,
  filters: Object,
})

const search = ref(props.filters.q || '')
const tipoFilter = ref(props.filters.tipo || '')

watch([search, tipoFilter], ([newQ, newTipo]) => {
  router.get(route('usuarios.index'), 
    { q: newQ, tipo: newTipo },
    { preserveState: true, replace: true }
  )
})

function eliminar(id) {
  if (confirm('¿Estás seguro de eliminar este usuario?')) {
    router.delete(route('usuarios.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Usuarios">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Usuarios</h2>
        <Link 
          :href="route('usuarios.create')" 
          class="px-4 py-2 rounded hover:opacity-90 transition"
          style="background-color: var(--color-primary); color: white;">
          + Nuevo
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-6" style="background-color: var(--color-base);">
          
          <div class="mb-4 grid md:grid-cols-2 gap-4">
            <input 
              v-model="search" 
              type="text" 
              placeholder="Buscar por nombre, email o teléfono..." 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <select 
              v-model="tipoFilter" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
              :style="{'--tw-ring-color': 'var(--color-primary)'}">
              <option value="">Todos los tipos</option>
              <option value="propietario">Propietario</option>
              <option value="barbero">Barbero</option>
              <option value="cliente">Cliente</option>
            </select>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y" style="border-color: var(--color-neutral);">
              <thead>
                <tr style="background-color: var(--color-secondary);">
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase" style="color: var(--color-base);">Nombre</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase" style="color: var(--color-base);">Email</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase" style="color: var(--color-base);">Teléfono</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase" style="color: var(--color-base);">Tipo</th>
                 
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase" style="color: var(--color-base);">Estado</th>
                  <th class="px-3 py-2 text-left text-xs font-medium uppercase" style="color: var(--color-base);">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y" style="border-color: var(--color-neutral); ">
                <tr v-for="u in usuarios.data" :key="u.id" class="hover:opacity-80 transition" style="background-color: var(--color-base);">
                  <td class="px-3 py-2 whitespace-nowrap" style="color: var(--color-neutral);">{{ u.name }}</td>
                  <td class="px-3 py-2 whitespace-nowrap" style="color: var(--color-neutral);">{{ u.email }}</td>
                  <td class="px-3 py-2 whitespace-nowrap" style="color: var(--color-neutral);">{{ u.telefono || '-' }}</td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    <span class="px-2 py-1 rounded text-xs" :style="{
                      'background-color': u.tipo_usuario === 'propietario' ? 'var(--color-primary)' : u.tipo_usuario === 'barbero' ? 'var(--color-accent)' : 'var(--color-secondary)',
                      'color': 'white'
                    }">
                      {{ u.tipo_usuario }}
                    </span>
                  </td>
                
                  <td class="px-3 py-2 whitespace-nowrap">
                    <span class="px-2 py-1 rounded text-xs" :style="{
                      'background-color': u.estado === 'activo' ? 'var(--color-success)' : 'var(--color-error)',
                      'color': 'white'
                    }">
                      {{ u.estado || 'activo' }}
                    </span>
                  </td>
                  <td class="px-3 py-2 whitespace-nowrap text-sm">
                    <Link 
                      :href="route('usuarios.edit', u.id)" 
                      class="px-2 py-1 rounded mr-2 hover:opacity-80 transition"
                      style="background-color: var(--color-accent); color: white;">
                      Editar
                    </Link>
                    <button 
                      @click="eliminar(u.id)" 
                      class="px-2 py-1 rounded hover:opacity-80 transition"
                      style="background-color: var(--color-error); color: white;">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="usuarios.data.length === 0" class="text-center py-8" style="color: var(--color-neutral); opacity: 0.5;">
            No se encontraron usuarios
          </div>

          <div v-if="usuarios.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link 
              v-for="l in usuarios.links" 
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
