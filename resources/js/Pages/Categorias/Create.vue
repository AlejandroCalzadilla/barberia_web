<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const form = useForm({
  nombre: '',
  descripcion: '',
  estado: 'activa',
})

function submit() {
  form.post(route('categorias.store'))
}
</script>

<template>
  <AppLayout title="Nueva categoría">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Nueva categoría</h2>
        <Link 
          :href="route('categorias.index')" 
          class="px-3 py-2 rounded hover:opacity-90 transition"
          style="background-color: var(--color-secondary); color: var(--color-base);">
          Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-6 grid gap-4" style="background-color: var(--color-base);">
          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Nombre</label>
            <input 
              v-model="form.nombre" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.nombre" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.nombre }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Descripción</label>
            <textarea 
              v-model="form.descripcion" 
              rows="4"
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            ></textarea>
            <div v-if="form.errors.descripcion" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.descripcion }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Estado</label>
            <select 
              v-model="form.estado" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="activa">Activa</option>
              <option value="inactiva">Inactiva</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.estado }}</div>
          </div>

          <div class="flex gap-2 mt-2">
            <button 
              @click="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 text-white rounded hover:opacity-90 transition disabled:opacity-50"
              style="background-color: var(--color-primary);">
              Guardar
            </button>
            <Link 
              :href="route('categorias.index')" 
              class="px-4 py-2 rounded hover:opacity-90 transition"
              style="background-color: var(--color-secondary); color: var(--color-base);">
              Cancelar
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
