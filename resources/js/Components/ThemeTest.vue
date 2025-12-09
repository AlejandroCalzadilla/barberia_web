<script setup>
import { useTheme } from '@/composables/useTheme.js';

const { getCurrentThemeInfo } = useTheme();
</script>

<template>
    <div class="p-6 space-y-6" style="background-color: var(--color-base);">
        <div class="text-center mb-6">
            <h3 class="text-3xl font-bold mb-2" style="color: var(--color-neutral);">
                🎨 Sistema de Temas Personalizado
            </h3>
            <p class="text-lg opacity-75" style="color: var(--color-neutral);">
                Tema actual: {{ getCurrentThemeInfo().name }}
            </p>
        </div>
        
        <!-- Botones principales -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button 
                class="px-6 py-4 rounded-xl font-bold text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                style="background-color: var(--color-primary);"
            >
                Primary
            </button>
            <button 
                class="px-6 py-4 rounded-xl font-bold text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                style="background-color: var(--color-secondary);"
            >
                Secondary
            </button>
            <button 
                class="px-6 py-4 rounded-xl font-bold text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                style="background-color: var(--color-accent);"
            >
                Accent
            </button>
            <button 
                class="px-6 py-4 rounded-xl font-bold text-white shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                style="background-color: var(--color-neutral);"
            >
                Neutral
            </button>
        </div>
        
        <!-- Card principal -->
        <div 
            class="p-8 rounded-2xl shadow-2xl border"
            style="background-color: var(--color-base); border-color: var(--color-primary);"
        >
            <h2 class="text-2xl font-bold mb-4" style="color: var(--color-primary);">
                Card con Tema Dinámico
            </h2>
            <p class="text-lg mb-6 opacity-80" style="color: var(--color-neutral);">
                Este contenido cambia automáticamente según el tema seleccionado. 
                Prueba cambiar entre los diferentes temas para ver el efecto.
            </p>
            
            <!-- Badges -->
            <div class="flex flex-wrap gap-3">
                <span 
                    class="px-3 py-1 rounded-full text-sm font-medium text-white"
                    style="background-color: var(--color-info);"
                >
                    Info Badge
                </span>
                <span 
                    class="px-3 py-1 rounded-full text-sm font-medium text-white"
                    style="background-color: var(--color-success);"
                >
                    Success Badge
                </span>
                <span 
                    class="px-3 py-1 rounded-full text-sm font-medium text-white"
                    style="background-color: var(--color-warning);"
                >
                    Warning Badge
                </span>
                <span 
                    class="px-3 py-1 rounded-full text-sm font-medium text-white"
                    style="background-color: var(--color-error);"
                >
                    Error Badge
                </span>
            </div>
        </div>
        
        <!-- Paleta de colores completa -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center">
                <div 
                    class="w-full h-32 rounded-xl mb-3 shadow-lg border-4 border-white"
                    style="background-color: var(--color-primary);"
                ></div>
                <h4 class="font-bold" style="color: var(--color-neutral);">Primary</h4>
                <p class="text-sm opacity-60" style="color: var(--color-neutral);">Principal</p>
            </div>
            
            <div class="text-center">
                <div 
                    class="w-full h-32 rounded-xl mb-3 shadow-lg border-4 border-white"
                    style="background-color: var(--color-secondary);"
                ></div>
                <h4 class="font-bold" style="color: var(--color-neutral);">Secondary</h4>
                <p class="text-sm opacity-60" style="color: var(--color-neutral);">Secundario</p>
            </div>
            
            <div class="text-center">
                <div 
                    class="w-full h-32 rounded-xl mb-3 shadow-lg border-4 border-white"
                    style="background-color: var(--color-accent);"
                ></div>
                <h4 class="font-bold" style="color: var(--color-neutral);">Accent</h4>
                <p class="text-sm opacity-60" style="color: var(--color-neutral);">Acento</p>
            </div>
            
            <div class="text-center">
                <div 
                    class="w-full h-32 rounded-xl mb-3 shadow-lg border-4 border-white"
                    style="background-color: var(--color-neutral);"
                ></div>
                <h4 class="font-bold" style="color: var(--color-neutral);">Neutral</h4>
                <p class="text-sm opacity-60" style="color: var(--color-neutral);">Neutral</p>
            </div>
        </div>
        
        <!-- Indicadores de estado -->
        <div class="flex justify-center">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full max-w-2xl">
                <div 
                    class="p-4 rounded-lg text-center text-white font-medium"
                    style="background-color: var(--color-info);"
                >
                    ℹ️ Info
                </div>
                <div 
                    class="p-4 rounded-lg text-center text-white font-medium"
                    style="background-color: var(--color-success);"
                >
                    ✅ Success
                </div>
                <div 
                    class="p-4 rounded-lg text-center text-white font-medium"
                    style="background-color: var(--color-warning);"
                >
                    ⚠️ Warning
                </div>
                <div 
                    class="p-4 rounded-lg text-center text-white font-medium"
                    style="background-color: var(--color-error);"
                >
                    ❌ Error
                </div>
            </div>
        </div>
    </div>
</template>