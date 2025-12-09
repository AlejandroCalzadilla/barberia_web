<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  reservas: Array,
  productos: Array,
  metodosPago: Array,
  tiposPago: Array,
  estadosPago: Array,
  hoy: String,
})

const form = useForm({
  id_reserva: '',
  monto: 0,
  fecha_pago: props.hoy,
  metodo_pago: 'efectivo',
  tipo: 'pago_completo',
  estado: 'pendiente',
  notas: '',
  productos: [],
})

const selectedReserva = ref(null)

// Actualizar el monto cuando se selecciona una reserva
watch(() => form.id_reserva, async (newVal) => {
  if (!newVal) {
    selectedReserva.value = null
    form.monto = 0
    return
  }
  
  const reserva = props.reservas.find(r => r.id_reserva == newVal)
  if (reserva) {
    selectedReserva.value = reserva
    // Si es un anticipo, establecer el monto del anticipo, de lo contrario el total
    form.monto = form.tipo === 'anticipo' ? (reserva.monto_anticipo || 0) : reserva.total
  }
})

// Actualizar el monto cuando cambia el tipo de pago
watch(() => form.tipo, (newVal) => {
  if (selectedReserva.value) {
    form.monto = newVal === 'anticipo' ? 
      (selectedReserva.value.monto_anticipo || 0) : 
      selectedReserva.value.total
  }
})

function addProducto() {
  form.productos.push({ id_producto: '', cantidad: 1, precio: 0 })
}

function removeProducto(index) {
  form.productos.splice(index, 1)
}

function onChangeProducto(index) {
  const producto = form.productos[index]
  const productoInfo = props.productos.find(p => p.id_producto == producto.id_producto)
  if (productoInfo) {
    producto.precio = productoInfo.precio_venta
  }
}

// Calcular el subtotal de productos
const subtotalProductos = computed(() => {
  return form.productos.reduce((sum, p) => {
    return sum + (Number(p.cantidad) * Number(p.precio))
  }, 0)
})

// Calcular el total
const total = computed(() => {
  return form.productos.length > 0 ? subtotalProductos.value : form.monto
})

// Actualizar el monto total cuando cambian los productos
watch(subtotalProductos, (newVal) => {
  if (form.productos.length > 0) {
    form.monto = newVal
  }
})

function submit() {
  // Si no hay productos, asegurarse de que productos sea un array vacío
  if (!form.productos || form.productos.length === 0) {
    form.productos = []
  }
  
  form.post(route('pagos.store'), {
    onSuccess: () => {
      // Redirigir al listado con mensaje de éxito
    },
    onError: (errors) => {
      console.error('Error al crear el pago:', errors)
    }
  })
}
</script>

<template>
  <AppLayout title="Nuevo pago">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo pago</h2>
        <Link :href="route('pagos.index')" class="px-3 py-2 bg-gray-200 rounded">Volver</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6 space-y-6">
          <div class="grid md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Reserva</label>
              <select v-model="form.id_reserva" class="w-full border rounded px-3 py-2">
                <option value="" disabled>Seleccione...</option>
                <option v-for="r in reservas" :key="r.id_reserva" :value="r.id_reserva">#{{ r.id_reserva }} - {{ r.cliente?.user?.name }} / {{ r.barbero?.user?.name }}</option>
              </select>
              <div v-if="form.errors.id_reserva" class="text-sm text-red-600 mt-1">{{ form.errors.id_reserva }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Método de pago</label>
              <select v-model="form.metodo_pago" class="w-full border rounded px-3 py-2">
                <option v-for="m in enums.metodo_pago" :key="m" :value="m">{{ m }}</option>
              </select>
              <div v-if="form.errors.metodo_pago" class="text-sm text-red-600 mt-1">{{ form.errors.metodo_pago }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Tipo de pago</label>
              <select v-model="form.tipo_pago" class="w-full border rounded px-3 py-2">
                <option v-for="t in enums.tipo_pago" :key="t" :value="t">{{ t }}</option>
              </select>
              <div v-if="form.errors.tipo_pago" class="text-sm text-red-600 mt-1">{{ form.errors.tipo_pago }}</div>
            </div>
          </div>

          <div class="grid md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Monto servicio</label>
              <input v-model.number="form.monto_servicio" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" />
              <div v-if="form.errors.monto_servicio" class="text-sm text-red-600 mt-1">{{ form.errors.monto_servicio }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Descuento</label>
              <input v-model.number="form.descuento" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" />
              <div v-if="form.errors.descuento" class="text-sm text-red-600 mt-1">{{ form.errors.descuento }}</div>
            </div>
            <div class="flex items-end">
              <div class="text-right w-full">
                <div class="text-sm text-gray-600">Monto productos: {{ montoProductos.toFixed(2) }}</div>
                <div class="text-lg font-semibold">Total: {{ total.toFixed(2) }}</div>
              </div>
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-semibold">Productos</h3>
              <button @click="addDetalle" class="px-3 py-2 bg-gray-800 text-white rounded">Agregar producto</button>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-left border-b">
                    <th class="p-2">Producto</th>
                    <th class="p-2">Cantidad</th>
                    <th class="p-2">Precio</th>
                    <th class="p-2">Subtotal</th>
                    <th class="p-2"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(d, i) in form.detalles" :key="i" class="border-b">
                    <td class="p-2">
                      <select v-model="d.id_producto" @change="onChangeProducto(i)" class="border rounded px-2 py-1">
                        <option value="" disabled>Seleccione...</option>
                        <option v-for="p in productos" :key="p.id_producto" :value="p.id_producto">{{ p.nombre }}</option>
                      </select>
                      <div v-if="form.errors[`detalles.${i}.id_producto`]" class="text-sm text-red-600 mt-1">{{ form.errors[`detalles.${i}.id_producto`] }}</div>
                    </td>
                    <td class="p-2">
                      <input v-model.number="d.cantidad" type="number" min="1" class="border rounded px-2 py-1 w-24" />
                      <div v-if="form.errors[`detalles.${i}.cantidad`]" class="text-sm text-red-600 mt-1">{{ form.errors[`detalles.${i}.cantidad`] }}</div>
                    </td>
                    <td class="p-2">
                      <input v-model.number="d.precio_unitario" type="number" step="0.01" min="0" class="border rounded px-2 py-1 w-28" />
                      <div v-if="form.errors[`detalles.${i}.precio_unitario`]" class="text-sm text-red-600 mt-1">{{ form.errors[`detalles.${i}.precio_unitario`] }}</div>
                    </td>
                    <td class="p-2">{{ (Number(d.cantidad||0) * Number(d.precio_unitario||0)).toFixed(2) }}</td>
                    <td class="p-2">
                      <button @click="removeDetalle(i)" class="text-red-600">Quitar</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Notas</label>
            <textarea v-model="form.notas" class="w-full border rounded px-3 py-2"></textarea>
          </div>

          <div class="flex gap-2">
            <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar</button>
            <Link :href="route('pagos.index')" class="px-4 py-2 bg-gray-200 rounded">Cancelar</Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
