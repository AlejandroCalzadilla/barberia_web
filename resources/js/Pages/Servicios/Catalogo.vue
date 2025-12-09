<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ServiciosCatalogo from '@/Components/ServiciosCatalogo.vue';
import { usePage } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
    <AppLayout title="Catálogo de Servicios">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Bienvenido, {{ page.props?.auth?.user?.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <ServiciosCatalogo />
            </div>
        </div>
    </AppLayout>
</template>
