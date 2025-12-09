<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  categorias: Array,
})

const previewImage = ref(null)

const form = useForm({
  id_categoria: '',
  codigo: '',
  nombre: '',
  descripcion: '',
  precio_compra: 0,
  precio_venta: 0,
  stock_actual: 0,
  stock_minimo: 0,
  unidad_medida: '',
  estado: 'activo',
  imagenurl: null,
})

function handleImageChange(event) {
  const file = event.target.files[0]
  if (file) {
    form.imagenurl = file
    const reader = new FileReader()
    reader.onload = (e) => {
      previewImage.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

function submit() {
  form.post(route('productos.store'))
}
</script>

<template>
  <AppLayout title="Nuevo producto">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Nuevo producto</h2>
        <Link 
          :href="route('productos.index')" 
          class="px-3 py-2 rounded hover:opacity-90 transition"
          style="background-color: var(--color-secondary); color: var(--color-base);">
          Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-6 grid gap-4 md:grid-cols-2" style="background-color: var(--color-base);">
          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Categoría</label>
            <select 
              v-model="form.id_categoria" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="" disabled>Seleccione...</option>
              <option v-for="c in categorias" :key="c.id_categoria" :value="c.id_categoria">{{ c.nombre }}</option>
            </select>
            <div v-if="form.errors.id_categoria" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_categoria }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Código</label>
            <input 
              v-model="form.codigo" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.codigo" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.codigo }}</div>
          </div>

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
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            ></textarea>
            <div v-if="form.errors.descripcion" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.descripcion }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Precio compra</label>
            <input 
              v-model.number="form.precio_compra" 
              type="number" 
              step="0.01" 
              min="0" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.precio_compra" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.precio_compra }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Precio venta</label>
            <input 
              v-model.number="form.precio_venta" 
              type="number" 
              step="0.01" 
              min="0" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.precio_venta" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.precio_venta }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Stock actual</label>
            <input 
              v-model.number="form.stock_actual" 
              type="number" 
              min="0" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.stock_actual" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.stock_actual }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Stock mínimo</label>
            <input 
              v-model.number="form.stock_minimo" 
              type="number" 
              min="0" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.stock_minimo" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.stock_minimo }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Unidad de medida</label>
            <input 
              v-model="form.unidad_medida" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.unidad_medida" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.unidad_medida }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Estado</label>
            <select 
              v-model="form.estado" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.estado }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Imagen</label>
            <input 
              type="file" 
              accept="image/*" 
              @change="handleImageChange"
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.imagenurl" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.imagenurl }}</div>
            <div v-if="previewImage" class="mt-3">
              <img :src="previewImage" alt="Preview" class="w-full h-auto rounded max-w-xs" />
            </div>
          </div>

          <div class="md:col-span-2 flex gap-2 mt-2">
            <button 
              @click="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 text-white rounded hover:opacity-90 transition disabled:opacity-50"
              style="background-color: var(--color-primary);">
              Guardar
            </button>
            <Link 
              :href="route('productos.index')" 
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
