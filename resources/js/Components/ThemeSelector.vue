<script setup>
import { ref } from 'vue';
import { useTheme } from '@/composables/useTheme.js';

const { theme, availableThemes, setTheme, getCurrentThemeInfo } = useTheme();
const isOpen = ref(false);

const handleThemeChange = (newTheme) => {
    setTheme(newTheme);
    isOpen.value = false;
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

// Cerrar al hacer click fuera
const closeDropdown = () => {
    isOpen.value = false;
};
</script>

<template>
    <div class="relative inline-block">
        <!-- Botón del selector -->
        <button
            @click="toggleDropdown"
            class="flex items-center gap-2 px-4 py-2 border rounded-lg shadow-sm  transition-all duration-200"
            style="background-color: var(--color-secondary); color: var(--color-base); border-color: var(--color-secondary);"
            >
            <span class="text-lg">{{ getCurrentThemeInfo().icon }}</span>
            <span class="hidden sm:inline text-sm font-medium">{{ getCurrentThemeInfo().name }}</span>
            <svg 
                class="w-4 h-4 ml-1 transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown -->
        <div 
            v-show="isOpen"
            @click="closeDropdown"
            class="fixed inset-0 z-10"
        ></div>
        
        <div 
            v-show="isOpen"
            class="absolute right-0 mt-2 w-52 border rounded-lg shadow-lg z-20"
            style="background-color: var(--color-base); border-color: var(--color-neutral); ;"
        >
            <div class="py-2">
                <button
                    v-for="themeOption in availableThemes"
                    :key="themeOption.value"
                    @click="handleThemeChange(themeOption.value)"
                    class="w-full flex items-center gap-3 px-4 py-2 text-left transition-all duration-200 hover:opacity-100"
                    :style="{
                        backgroundColor: theme === themeOption.value ? 'var(--color-primary)' : 'transparent',
                        color: theme === themeOption.value ? 'var(--color-base)' : 'var(--color-neutral)',
                        opacity: theme === themeOption.value ? 1 : 0.7,
                    }"
                >
                    <span class="text-lg">{{ themeOption.icon }}</span>
                    <span class="flex-1 text-sm font-medium">{{ themeOption.name }}</span>
                    <svg 
                        v-if="theme === themeOption.value"
                        class="w-4 h-4"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>