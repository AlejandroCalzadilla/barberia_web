<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ServiciosCatalogo from '@/Components/ServiciosCatalogo.vue';
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useTheme } from '@/composables/useTheme'
import { useRoles } from '@/composables/useRoles.js'

const { isPropietario, isBarbero, isCliente: isUserCliente } = useRoles()

const { theme, availableThemes, setTheme } = useTheme()

const quickLinks = [
  { key: 'categorias', label: 'Categorías', route: 'categorias.index', roles: ['propietario'], icon: '📁' },
  { key: 'productos', label: 'Productos', route: 'productos.index', roles: ['propietario'], icon: '📦' },
  { key: 'servicios', label: 'Servicios', route: 'servicios.index', roles: ['propietario'], icon: '✂️' },
  { key: 'barberos', label: 'Barberos', route: 'barberos.index', roles: ['propietario'], icon: '👤' },
]

const hasRole = (roles) => {
  if (!roles || roles.length === 0) return true
  return roles.some(role => {
    if (role === 'propietario') return isPropietario.value
    if (role === 'barbero') return isBarbero.value
    if (role === 'cliente') return isUserCliente.value
    return false
  })
}

const visibleQuickLinks = computed(() => quickLinks.filter(link => hasRole(link.roles)))


</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl leading-tight" :style="{ color: 'var(--color-neutral)' }">
                {{ isCliente ? 'Bienvenido, ' + (page.props?.auth?.user?.name || '') : 'Dashboard - Panel de Administración' }}
            </h2>
        </template>

        <!-- Vista para clientes - Catálogo de servicios -->
        <ServiciosCatalogo v-if="isUserCliente" />

        <!-- Vista para admin - Contenido del Dashboard -->
        <div v-else class="p-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Resumen de reportes -->
                <div 
                    class="shadow sm:rounded-lg p-6"
                    :style="{ backgroundColor: 'var(--color-base)' }"
                >
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold" :style="{ color: 'var(--color-neutral)' }">
                            📊 Reportes y Estadísticas
                        </h3>
                        <Link 
                            :href="route('reportes.index')"
                            class="px-4 py-2 rounded-lg font-medium transition-all duration-200 hover:shadow-md"
                            :style="{
                                backgroundColor: 'var(--color-primary)',
                                color: 'white'
                            }"
                        >
                            Ver Reportes Completos →
                        </Link>
                    </div>
                    
                    <p class="mb-6" :style="{ color: 'var(--color-neutral-light)' }">
                        Vista general del rendimiento del sistema. Para análisis detallados y más opciones, accede a los reportes completos.
                    </p>

                    <!-- Mini cards resumen -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div 
                            v-for="(stat, idx) in [
                                { label: 'Total Reservas', value: '0', icon: '📅', color: '#3b82f6' },
                                { label: 'Ingresos', value: 'Bs 0', icon: '💰', color: '#10b981' },
                                { label: 'Clientes', value: '0', icon: '👥', color: '#f59e0b' },
                                { label: 'Servicios', value: '0', icon: '✂️', color: '#8b5cf6' }
                            ]" 
                            :key="idx"
                            class="p-4 rounded-lg shadow-sm"
                            :style="{ 
                                backgroundColor: 'var(--color-base)',
                                borderLeft: `4px solid ${stat.color}`
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm" :style="{ color: 'var(--color-neutral-light)' }">
                                        {{ stat.label }}
                                    </p>
                                    <p class="text-2xl font-bold mt-1" :style="{ color: 'var(--color-neutral)' }">
                                        {{ stat.value }}
                                    </p>
                                </div>
                                <span class="text-3xl">{{ stat.icon }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acceso rápido a gestión -->
                <div 
                    class="shadow sm:rounded-lg p-6"
                    :style="{ backgroundColor: 'var(--color-base)' }"
                >
                    <h3 class="text-xl font-bold mb-4" :style="{ color: 'var(--color-neutral)' }">
                        ⚡ Accesos Rápidos
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <Link
                            v-for="link in visibleQuickLinks"
                            :key="link.key"
                            :href="route(link.route)"
                            class="p-4 rounded-lg border-2 transition-all duration-200 hover:shadow-md hover:scale-105"
                            :style="{
                                backgroundColor: 'var(--color-base)',
                                borderColor: 'var(--color-primary-light)',
                                color: 'var(--color-neutral)'
                            }"
                        >
                            <div class="text-center">
                                <div class="text-3xl mb-2">{{ link.icon }}</div>
                                <div class="font-medium">{{ link.label }}</div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

