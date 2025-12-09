<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeSelector from '@/Components/ThemeSelector.vue';
import SearchBar from '@/Components/SearchBar.vue';
import PageViewCounter from '@/Components/PageViewCounter.vue';
import { useTheme } from '@/composables/useTheme.js';

defineProps({
    title: String,
});

// Estado global de búsqueda
const globalSearch = ref('')

// Emitir evento de búsqueda para que las vistas puedan escucharlo
const handleGlobalSearch = (value) => {
    globalSearch.value = value
    // Emitir evento personalizado para que las vistas lo escuchen
    window.dispatchEvent(new CustomEvent('global-search', { detail: value }))
}

const showingNavigationDropdown = ref(false);

// Inicializar el tema global
const { theme, getCurrentThemeInfo } = useTheme();
const isKidsTheme = computed(() => theme.value === 'kids')

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

// Sidebar
const page = usePage()
const user = computed(() => page.props?.auth?.user || {})
const isPropietario = computed(() => user.value?.is_propietario || false)
const isBarbero = computed(() => user.value?.is_barbero || false)
const isCliente = computed(() => user.value?.is_cliente || false)
const sidebarOpen = ref(true)

const links = computed(() => [
    //{ key: 'dashboard', label: 'Dashboard', route: 'dashboard', roles: ['propietario', 'barbero'], icon: '🏠' },
    { key: 'reportes', label: 'Reportes', route: 'reportes.index', roles: ['propietario'], icon: '📊' },
   // { key: 'estadisticas-visitas', label: 'Estadísticas de Visitas', route: 'estadisticas.visitas', roles: ['propietario'], icon: '👁️' },
    { key: 'categorias', label: 'Categorías', route: 'categorias.index', roles: ['propietario'], icon: '📁' },
    { key: 'productos', label: 'Productos', route: 'productos.index', roles: ['propietario'], icon: '📦' },
    { key: 'servicios', label: 'Servicios', route: 'servicios.index', roles: ['propietario'], icon: '✂️' },
    //{ key: 'barberos', label: 'Barberos', route: 'barberos.index', roles: ['propietario'], icon: '👤' },
   // { key: 'clientes', label: 'Clientes', route: 'clientes.index', roles: ['propietario'], icon: '👥' },
    { key: 'horarios', label: 'Horarios', route: 'horarios.index', roles: ['propietario','barbero'], icon: '🕐' },
    { key: 'reservas', label: 'Reservas', route: 'reservas.index', roles: ['propietario', 'barbero', 'cliente'], icon: '📅' },
    { key: 'pagos', label: 'Pagos', route: 'pagos.index', roles: ['propietario', 'barbero', 'cliente'], icon: '💰' },
    { key: 'usuarios', label: 'Usuarios', route: 'usuarios.index', roles: ['propietario'], icon: '👨‍💼' },
    { key: 'servicio-catalogo', label: 'Catálogo de Servicios', route: 'servicios.catalogo', roles: ['cliente'], icon: '📋' }
])

const hasRole = (roles) => {
    if (!roles || roles.length === 0) return true
    return roles.some(role => {
        if (role === 'propietario') return isPropietario.value
        if (role === 'barbero') return isBarbero.value
        if (role === 'cliente') return isCliente.value
        return false
    })
}

const visibleLinks = computed(() => links.value.filter(link => hasRole(link.roles)))

</script>

<template>
    <div style="color: var(--color-primary);">

        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900" style="background-color: var(--color-base);">
            <nav class="" style="background-color: var(--color-sidebarBg);">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="background-color: var(--color-sidebarBg);">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                <div class=" flex justify-center">
                                    <img src="https://mail.tecnoweb.org.bo/inf513/grupo11sa/proyecto2/public/storage/logo3.png" alt="Evolution Barber Studio"
                                        class="h-32 w-auto object-contain" />
                                            </div>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                                <!-- Búsqueda Global -->
                                <div class="flex items-center" style="min-width: 300px;">
                                    <SearchBar v-model="globalSearch" @search="handleGlobalSearch"
                                        placeholder="Buscar... (Ctrl+F)" />
                                </div>

                                <!-- Theme Selector -->
                                <div class="flex items-center">
                                    <ThemeSelector />
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.current_team.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink
                                                :href="route('teams.show', $page.props.auth.user.current_team)">
                                                Team Settings
                                            </DropdownLink>

                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams"
                                                :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                <div class="border-t border-gray-200 dark:border-gray-600" />

                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Switch Teams
                                                </div>

                                                <template v-for="team in $page.props.auth.user.all_teams"
                                                    :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                                    class="me-2 h-5 w-5 text-green-400"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                                    stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>

                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos"
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="h-8 w-8 rounded-full object-cover"
                                                :src="$page.props.auth.user.profile_photo_url"
                                                :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures"
                                            :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200 dark:border-gray-600" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out"
                                @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <!-- Links móviles se mostrarán en el sidebar -->
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="h-10 w-10 rounded-full object-cover"
                                    :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200 dark:border-gray-600" />

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)"
                                    :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams"
                                    :href="route('teams.create')" :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200 dark:border-gray-600" />

                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                        class="me-2 h-5 w-5 text-green-400"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" :class="{ 'kids-pattern-bg': isKidsTheme }" :style="{
                backgroundColor: isKidsTheme ? undefined : 'var(--color-sidebarBg)',
                borderColor: 'var(--color-primary)'
            }">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content con Sidebar para todos los usuarios -->
            <div class="flex min-h-screen">
                <!-- Sidebar -->
                <aside 
                    :class="['transition-all duration-300', sidebarOpen ? 'w-64' : 'w-16']" 
                    >
                    <div class="h-full flex flex-col">
                        <!-- Toggle button -->
                        <div class="p-4 flex justify-end" style="background-color: var(--color-sidebarBg);">
                            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded hover:bg-opacity-10" :style="{
                                color: 'var(--color-primary)',
                                backgroundColor: 'transparent'
                            }">
                                <svg class="w-6 h-6 transition-transform duration-300"
                                    :class="{ 'rotate-180': !sidebarOpen }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Navigation Links -->
                        <nav class="flex-1 px-3 space-y-2" :class="{ 'kids-pattern-bg': isKidsTheme }" :style="{
                            backgroundColor: isKidsTheme ? undefined : 'var(--color-sidebarBg)'
                        }">
                            <template v-for="link in visibleLinks" :key="link.key">
                                <Link :href="route(link.route)" :class="[
                                    'flex items-center px-2 py-2 rounded-lg transition-all duration-200',
                                    { 'kids-pattern-bg': isKidsTheme && !route().current(link.route + '*') },
                                    route().current(link.route + '*')
                                        ? 'font-semibold shadow-md'
                                        : 'hover:shadow-sm'
                                ]" :style="{
                                        backgroundColor: route().current(link.route + '*')
                                            ? 'var(--color-primary)'
                                            : isKidsTheme ? undefined : 'transparent',
                                        color: route().current(link.route + '*')
                                            ? 'white'
                                            : 'var(--color-neutral)'
                                    }">
                                <span class="text-2xl">{{ link.icon }}</span>
                                <span v-show="sidebarOpen" class="ml-3 transition-opacity duration-300"
                                    :class="{ 'opacity-0': !sidebarOpen }">
                                    {{ link.label }}
                                </span>
                                </Link>
                            </template>
                        </nav>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-1 relative" :class="{ 'kids-animated-bg': isKidsTheme }" :style="{
                    backgroundColor: isKidsTheme ? undefined : 'var(--color-base-light)',
                    zIndex: isKidsTheme ? 5 : 'auto'
                }">
                    <!-- Figuras flotantes solo para tema niños -->
                    <div v-if="isKidsTheme" class="floating-shapes">
                        <div class="shape">⭐</div>
                        <div class="shape">🎈</div>
                        <div class="shape">💖</div>
                        <div class="shape">🚀</div>
                        <div class="shape">☁️</div>
                        <div class="shape">🍭</div>
                        <div class="shape">☀️</div>
                        <div class="shape">🧸</div>
                    </div>

                    <!-- Contenido de la app -->
                    <div :style="{ position: 'relative', zIndex: 10 }">
                        <slot />
                    </div>

                    <!-- Contador de visitas (esquina inferior derecha) -->
                    <PageViewCounter position="bottom-right" />
                </main>
            </div>
        </div>
    </div>
</template>
