<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme.js';
import ThemeSelector from '@/Components/ThemeSelector.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const { theme } = useTheme();

const services = [
    { 
        icon: '✂️',
        title: 'Cortes de Cabello',
        description: 'Cortes clásicos y modernos adaptados a tu estilo personal y tipo de cabello.'
    },
    {
        icon: '💈',
        title: 'Afeitado Clásico',
        description: 'Experiencia tradicional con toallas calientes y productos premium de primera calidad.'
    },
    {
        icon: '🧔',
        title: 'Arreglo de Barba',
        description: 'Diseño y mantenimiento profesional para mantener tu barba impecable.'
    },
    {
        icon: '👨',
        title: 'Tratamientos',
        description: 'Cuidado especializado para mantener tu cabello y barba saludables.'
    }
]

const features = [
    { icon: '📅', title: 'Reservas Online', text: 'Agenda 24/7 desde cualquier dispositivo' },
    { icon: '👥', title: 'Profesionales', text: 'Barberos expertos y certificados' },
    { icon: '💰', title: 'Precios Justos', text: 'Calidad premium accesible' },
    { icon: '⭐', title: 'Ambiente Premium', text: 'Espacio moderno y acogedor' }
]
</script>

<template>
    <Head title="Evolution Barber Studio" />

    <div class="relative min-h-screen" 
         :style="{ 
             backgroundColor: 'var(--color-base)',
             backgroundImage: 'radial-gradient(var(--color-primary-light) 1px, transparent 1px)',
             backgroundSize: '30px 30px'
         }">
        
        <!-- Theme Selector -->
        <div class="absolute top-4 left-4 z-20">
            <ThemeSelector />
        </div>
       
        <!-- Auth Links -->
        <div v-if="canLogin" class="absolute top-0 right-0 p-6 text-right z-10">
            <Link 
                v-if="$page.props.auth.user" 
                :href="route('dashboard')" 
                class="font-semibold px-4 py-2 rounded-lg transition-all hover:shadow-lg"
                :style="{
                    color: 'var(--color-primary)',
                    backgroundColor: 'var(--color-base)'
                }"
            >
                Dashboard
            </Link>

            <template v-else>
                <Link 
                    :href="route('login')" 
                    class="font-semibold px-4 py-2 rounded-lg transition-all hover:shadow-lg"
                    :style="{
                        color: 'var(--color-neutral)',
                        backgroundColor: 'transparent'
                    }"
                >
                    Iniciar Sesión
                </Link>

                <Link 
                    v-if="canRegister" 
                    :href="route('register')" 
                    class="ms-4 font-semibold px-4 py-2 rounded-lg transition-all hover:shadow-lg"
                    :style="{
                        backgroundColor: 'var(--color-primary)',
                        color: 'white'
                    }"
                >
                    Registrarse
                </Link>
            </template>
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16">
            <!-- Hero Section -->
            <div class="text-center mb-20 ">
                <div class="mb-8 flex justify-center">
                    <img 
                        src="/storage/logo3.png" 
                        alt="Evolution Barber Studio"
                        class="h-90 w-auto object-contain"
                    />
                </div>
                <h1 class="text-5xl lg:text-7xl font-bold mb-6" 
                    :style="{ color: 'var(--color-neutral)' }">
                    Evolution Barber Studio
                </h1>
                <p class="text-xl lg:text-2xl mb-8 max-w-3xl mx-auto" 
                   :style="{ color: 'var(--color-neutral-light)' }">
                    Donde el estilo se encuentra con la tradición. Experimenta el arte de la barbería 
                    con nuestros profesionales expertos.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <Link
                        :href="canLogin ? route('register') : '#'"
                        class="px-8 py-4 rounded-xl font-bold text-white text-lg transition-all duration-300 hover:shadow-2xl hover:scale-105"
                        :style="{ backgroundColor: 'var(--color-primary)' }"
                    >
                        📅 Reservar Cita
                    </Link>
                    <Link
                        :href="canLogin ? route('login') : '#'"
                        class="px-8 py-4 rounded-xl font-bold text-lg transition-all duration-300 hover:shadow-2xl hover:scale-105"
                        :style="{
                            backgroundColor: 'var(--color-base)',
                            border: '2px solid var(--color-primary)',
                            color: 'var(--color-primary)'
                        }"
                    >
                        Ver Servicios
                    </Link>
                </div>
            </div>

            <!-- Servicios -->
            <div class="mb-20">
                <h2 class="text-4xl font-bold text-center mb-12" 
                    :style="{ color: 'var(--color-neutral)' }">
                    ✂️ Nuestros Servicios
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="(service, idx) in services" 
                        :key="idx"
                        class="p-8 rounded-2xl border-2 transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                        :style="{
                            backgroundColor: 'var(--color-base)',
                            borderColor: 'var(--color-primary-light)'
                        }"
                    >
                        <div class="text-6xl mb-4 text-center">{{ service.icon }}</div>
                        <h3 class="text-xl font-bold mb-3 text-center" 
                            :style="{ color: 'var(--color-neutral)' }">
                            {{ service.title }}
                        </h3>
                        <p class="text-center" :style="{ color: 'var(--color-neutral-light)' }">
                            {{ service.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="mb-20">
                <h2 class="text-4xl font-bold text-center mb-12" 
                    :style="{ color: 'var(--color-neutral)' }">
                    ⭐ ¿Por Qué Elegirnos?
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="(feature, idx) in features" 
                        :key="idx"
                        class="p-6 rounded-xl text-center transition-all duration-300 hover:shadow-lg"
                        :style="{
                            backgroundColor: 'var(--color-base)',
                            border: '2px solid var(--color-primary-light)'
                        }"
                    >
                        <div class="text-5xl mb-3">{{ feature.icon }}</div>
                        <h3 class="text-lg font-bold mb-2" :style="{ color: 'var(--color-neutral)' }">
                            {{ feature.title }}
                        </h3>
                        <p class="text-sm" :style="{ color: 'var(--color-neutral-light)' }">
                            {{ feature.text }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="text-center p-12 rounded-2xl border-2 mb-12"
                 :style="{
                     background: 'linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%)',
                     borderColor: 'var(--color-primary)'
                 }">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                    ¿Listo para tu transformación? 💈
                </h2>
                <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                    No esperes más. Reserva tu cita ahora y descubre por qué somos 
                    la barbería favorita de miles de clientes.
                </p>
                <Link
                    :href="canLogin ? route('register') : '#'"
                    class="inline-block px-10 py-4 rounded-xl font-bold text-lg transition-all duration-300 hover:shadow-2xl hover:scale-110"
                    :style="{
                        backgroundColor: 'white',
                        color: 'var(--color-primary)'
                    }"
                >
                    Agendar Mi Cita Ahora →
                </Link>
            </div>

            <!-- Footer -->
            <div class="flex flex-col sm:flex-row justify-between items-center pt-8 border-t-2" 
                 :style="{ borderColor: 'var(--color-primary-light)' }">
                <div class="text-sm mb-4 sm:mb-0" :style="{ color: 'var(--color-neutral-light)' }">
                    © 2025 Barbería Premium. Todos los derechos reservados.
                </div>
                <div class="text-sm" :style="{ color: 'var(--color-neutral-light)' }">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </div>
            </div>
        </div>
    </div>
</template>
