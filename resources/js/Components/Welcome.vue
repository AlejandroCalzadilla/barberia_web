<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { useTheme } from '@/composables/useTheme.js';

const { theme } = useTheme();

const services = [
    { 
        icon: '✂️',
        title: 'Cortes de Cabello',
        description: 'Cortes clásicos y modernos adaptados a tu estilo. Nuestros barberos expertos te ayudarán a encontrar el look perfecto que se adapte a tu personalidad y estilo de vida.'
    },
    {
        icon: '💈',
        title: 'Afeitado Clásico',
        description: 'Experiencia de afeitado tradicional con toallas calientes y productos premium. Disfruta de un momento de relajación mientras dejamos tu rostro impecable.'
    },
    {
        icon: '🧔',
        title: 'Arreglo de Barba',
        description: 'Diseño y mantenimiento profesional de barba. Desde recortes precisos hasta moldeado completo, mantenemos tu barba con estilo y bien cuidada.'
    },
    {
        icon: '👨',
        title: 'Tratamientos Capilares',
        description: 'Cuidado especializado para mantener tu cabello saludable. Ofrecemos tratamientos hidratantes, restauradores y productos de calidad premium.'
    }
]

const features = [
    {
        icon: '📅',
        title: 'Reservas Online',
        description: 'Sistema de reservas 24/7 para tu comodidad. Agenda tu cita cuando quieras, desde donde estés.'
    },
    {
        icon: '👥',
        title: 'Barberos Profesionales',
        description: 'Equipo capacitado con años de experiencia. Cada barbero es un artista dedicado a su oficio.'
    },
    {
        icon: '💰',
        title: 'Precios Accesibles',
        description: 'Calidad premium a precios justos. Ofrecemos servicios de alta gama sin comprometer tu presupuesto.'
    },
    {
        icon: '⭐',
        title: 'Ambiente Premium',
        description: 'Espacio moderno y acogedor diseñado para tu confort. Música, bebidas y un ambiente relajado.'
    }
]
</script>

<template>
    <div class="border-2 rounded-xl overflow-hidden" style="background-color: var(--color-base); border-color: var(--color-primary);">
        <!-- Header Hero -->
        <div class="relative p-8 lg:p-12" 
             style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);">
            <ApplicationLogo class="block h-16 w-auto mb-8" />
            
            <div class="max-w-3xl">
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                    Bienvenido a tu Barbería Premium
                </h1>
                <p class="text-xl text-white/90 leading-relaxed">
                    Más que un corte de cabello, una experiencia. Descubre el arte de la barbería tradicional 
                    combinado con las tendencias más modernas. Tu estilo, nuestra pasión.
                </p>
            </div>

            <!-- Decorative scissors icon -->
            <div class="absolute top-8 right-8 text-white/10 text-9xl hidden lg:block">
                ✂️
            </div>
        </div>

        <!-- Nuestros Servicios -->
        <div class="p-8 lg:p-12" style="background-color: var(--color-base);">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold mb-3" style="color: var(--color-neutral);">
                    💈 Nuestros Servicios
                </h2>
                <p class="text-lg" style="color: var(--color-neutral-light);">
                    Servicios profesionales diseñados para el hombre moderno
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <div 
                    v-for="(service, idx) in services" 
                    :key="idx"
                    class="p-6 rounded-xl border-2 transition-all duration-300 hover:scale-105 hover:shadow-xl"
                    :style="{
                        backgroundColor: 'var(--color-base)',
                        borderColor: 'var(--color-primary-light)'
                    }"
                >
                    <div class="flex items-start space-x-4">
                        <div class="text-5xl flex-shrink-0">{{ service.icon }}</div>
                        <div>
                            <h3 class="text-xl font-bold mb-2" style="color: var(--color-neutral);">
                                {{ service.title }}
                            </h3>
                            <p class="leading-relaxed" style="color: var(--color-neutral-light);">
                                {{ service.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Por Qué Elegirnos -->
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold mb-3" style="color: var(--color-neutral);">
                    ⭐ ¿Por Qué Elegirnos?
                </h2>
                <p class="text-lg" style="color: var(--color-neutral-light);">
                    La mejor experiencia en barbería a tu alcance
                </p>
            </div>

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
                    <div class="text-5xl mb-4">{{ feature.icon }}</div>
                    <h3 class="text-lg font-bold mb-2" style="color: var(--color-neutral);">
                        {{ feature.title }}
                    </h3>
                    <p class="text-sm leading-relaxed" style="color: var(--color-neutral-light);">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Call to Action Footer -->
        <div class="p-8 lg:p-12 text-center border-t-2" 
             :style="{ 
                 backgroundColor: 'var(--color-base)',
                 borderColor: 'var(--color-primary-light)'
             }">
            <h3 class="text-2xl font-bold mb-4" style="color: var(--color-neutral);">
                ¿Listo para tu nuevo look? 📅
            </h3>
            <p class="text-lg mb-6" style="color: var(--color-neutral-light);">
                Agenda tu cita ahora y experimenta el mejor servicio de barbería
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a 
                    href="#" 
                    class="px-8 py-3 rounded-lg font-bold text-white transition-all duration-300 hover:shadow-xl hover:scale-105"
                    style="background-color: var(--color-primary);"
                >
                    📞 Reservar Ahora
                </a>
                <a 
                    href="#" 
                    class="px-8 py-3 rounded-lg font-bold transition-all duration-300 hover:shadow-xl hover:scale-105"
                    :style="{
                        backgroundColor: 'var(--color-base)',
                        border: '2px solid var(--color-primary)',
                        color: 'var(--color-primary)'
                    }"
                >
                    Ver Servicios
                </a>
            </div>
        </div>
    </div>
</template>
