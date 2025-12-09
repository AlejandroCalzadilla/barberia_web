<script setup>
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { useRoles } from '@/composables/useRoles.js'

const props = defineProps({
  productos: Object,
  categorias: Array,
  filters: Object,
})

const q = ref(props.filters?.q || '')
const categoria = ref(props.filters?.categoria || '')
const { isPropietario } = useRoles()

function search() {
  router.get(route('productos.index'), { q: q.value, categoria: categoria.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar producto?')) {
    router.delete(route('productos.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Productos">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Productos</h2>
        <Link v-if="isPropietario" :href="route('productos.create')" 
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
              placeholder="Buscar por nombre/código" 
              class="border rounded px-3 py-2 w-full" 
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
            />
            <select 
              v-model="categoria" 
              class="border rounded px-3 py-2"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);">
              <option value="">Todas</option>
              <option v-for="c in categorias" :key="c.id_categoria" :value="c.id_categoria">{{ c.nombre }}</option>
            </select>
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
                  <th class="p-2" style="color: var(--color-neutral);">Código</th>
                  <th class="p-2" style="color: var(--color-neutral);">Nombre</th>
                  <th class="p-2" style="color: var(--color-neutral);">Categoría</th>
                  <th class="p-2" style="color: var(--color-neutral);">Precio</th>
                  <th class="p-2" style="color: var(--color-neutral);">Estado</th>
                  <th class="p-2" style="color: var(--color-neutral);">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in productos.data" :key="p.id_producto" class="border-b" style="border-color: var(--color-neutral);">
                  <td class="p-2" style="color: var(--color-neutral);">{{ p.id_producto }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ p.codigo }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ p.nombre }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ p.categoria?.nombre }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ p.precio_venta }}</td>
                  <td class="p-2">
                    <span class="px-2 py-1 rounded" style="background-color: var(--color-accent); color: var(--color-base); opacity: 0.8;">
                      {{ p.estado }}
                    </span>
                  </td>
                  <td class="p-2 flex gap-2">
                    <Link 
                      v-if="isPropietario" 
                      :href="route('productos.edit', p.id_producto)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-primary);">
                      Editar
                    </Link>
                    <button 
                      v-if="isPropietario" 
                      @click="destroyItem(p.id_producto)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-error);">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="productos.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link 
              v-for="l in productos.links" 
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
