<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, ref } from 'vue'

const previewImage = ref(null)

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  telefono: '',
  direccion: '',
  tipo_usuario: 'cliente',
  estado: 'activo',
  // Campos barbero
  especialidad: '',
  foto_perfil: null,
  // Campos cliente
  fecha_nacimiento: '',
  ci: '',
})

const mostrarCamposBarbero = computed(() => form.tipo_usuario === 'barbero')
const mostrarCamposCliente = computed(() => form.tipo_usuario === 'cliente')

function handleImageChange(event) {
  const file = event.target.files[0]
  if (file) {
    form.foto_perfil = file
    const reader = new FileReader()
    reader.onload = (e) => {
      previewImage.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

function submit() {
  form.post(route('usuarios.store'))
}
</script>

<template>
  <AppLayout title="Nuevo usuario">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Nuevo usuario</h2>
        <Link 
          :href="route('usuarios.index')" 
          class="px-3 py-2 rounded hover:opacity-90 transition"
          style="background-color: var(--color-secondary); color: var(--color-base);">
          Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-6 grid gap-4 md:grid-cols-2" style="background-color: var(--color-base);">
          
          <div class="md:col-span-2">
            <h3 class="font-semibold mb-4" style="color: var(--color-neutral);">Información de acceso</h3>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Nombre completo *</label>
            <input 
              v-model="form.name" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.name" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.name }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Email *</label>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.email" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.email }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Contraseña *</label>
            <input 
              v-model="form.password" 
              type="password" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.password" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.password }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Confirmar contraseña *</label>
            <input 
              v-model="form.password_confirmation" 
              type="password" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
          </div>

          <div class="md:col-span-2">
            <h3 class="font-semibold mb-4 mt-4" style="color: var(--color-neutral);">Información personal</h3>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Teléfono</label>
            <input 
              v-model="form.telefono" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.telefono" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.telefono }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Dirección</label>
            <input 
              v-model="form.direccion" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.direccion" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.direccion }}</div>
          </div>

          <div class="md:col-span-2">
            <h3 class="font-semibold mb-4 mt-4" style="color: var(--color-neutral);">Configuración</h3>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Tipo de usuario *</label>
            <select 
              v-model="form.tipo_usuario" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}">
              <option value="propietario">Propietario</option>
              <option value="barbero">Barbero</option>
              <option value="cliente">Cliente</option>
            </select>
            <div v-if="form.errors.tipo_usuario" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.tipo_usuario }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Estado</label>
            <select 
              v-model="form.estado" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.estado }}</div>
          </div>

          <!-- Campos específicos para Barbero -->
          <template v-if="mostrarCamposBarbero">
            <div class="md:col-span-2">
              <h3 class="font-semibold mb-4 mt-4" style="color: var(--color-neutral);">🔧 Datos de Barbero</h3>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Especialidad</label>
              <textarea 
                v-model="form.especialidad" 
                rows="2"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
                style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
                :style="{'--tw-ring-color': 'var(--color-primary)'}"
                placeholder="Ej: Cortes clásicos y afeitados tradicionales"
              />
              <div v-if="form.errors.especialidad" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.especialidad }}</div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Foto de perfil</label>
              <input 
                type="file" 
                accept="image/*" 
                @change="handleImageChange"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
                style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
                :style="{'--tw-ring-color': 'var(--color-primary)'}"
              />
              <div v-if="form.errors.foto_perfil" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.foto_perfil }}</div>
              <div v-if="previewImage" class="mt-3">
                <img :src="previewImage" alt="Preview" class="w-full h-auto rounded max-w-xs" />
              </div>
            </div>
          </template>

          <!-- Campos específicos para Cliente -->
          <template v-if="mostrarCamposCliente">
            <div class="md:col-span-2">
              <h3 class="font-semibold mb-4 mt-4" style="color: var(--color-neutral);">👤 Datos de Cliente</h3>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Fecha de nacimiento</label>
              <input 
                v-model="form.fecha_nacimiento" 
                type="date" 
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
                style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
                :style="{'--tw-ring-color': 'var(--color-primary)'}"
              />
              <div v-if="form.errors.fecha_nacimiento" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.fecha_nacimiento }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">CI (Cédula de Identidad)</label>
              <input 
                v-model="form.ci" 
                type="text" 
                class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
                style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
                :style="{'--tw-ring-color': 'var(--color-primary)'}"
                placeholder="12345678"
              />
              <div v-if="form.errors.ci" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.ci }}</div>
            </div>
          </template>

          <div class="md:col-span-2 flex gap-2 mt-4">
            <button 
              @click="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 text-white rounded hover:opacity-90 transition disabled:opacity-50"
              style="background-color: var(--color-primary);">
              Guardar
            </button>
            <Link 
              :href="route('usuarios.index')" 
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
