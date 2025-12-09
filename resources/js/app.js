import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
// Cámbialo por esto:
import { ZiggyVue } from 'ziggy-js';
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Configuración de temas
const themeConfigs = {
    light: {
        primary: '#3b82f6',
        secondary: '#8b5cf6',
        accent: '#06b6d4',
        neutral: '#374151',
        base: '#ffffff',
        info: '#0ea5e9',
        success: '#22c55e',
        warning: '#f59e0b',
        error: '#ef4444',
    },
    dark: {
        primary: '#60a5fa',
        secondary: '#151c25ff',
        accent: '#22d3ee',
        neutral: '#e5e7eb',
        base: '#1f2937',

        info: '#38bdf8',
        success: '#4ade80',
        warning: '#fbbf24',
        error: '#f87171',
    },
    cupcake: {
        primary: '#ec4899',
        secondary: '#f97316',
        accent: '#06b6d4',
        neutral: '#64748b',
        base: '#fef7f0',
        info: '#0ea5e9',
        success: '#22c55e',
        warning: '#f59e0b',
        error: '#ef4444',
    },
    emerald: {
        primary: '#10b981',
        secondary: '#059669',
        accent: '#06b6d4',
        neutral: '#374151',
        base: '#ecfdf5',
        info: '#0ea5e9',
        success: '#22c55e',
        warning: '#f59e0b',
        error: '#ef4444',
    },
    synthwave: {
        primary: '#e879f9',
        secondary: '#a855f7',
        accent: '#06b6d4',
        neutral: '#f3e8ff',
        base: '#1e1b4b',
        info: '#8b5cf6',
        success: '#22c55e',
        warning: '#f59e0b',
        error: '#ef4444',
    }
};

// Inicializar el tema antes de que se cargue la aplicación
if (typeof window !== 'undefined') {
    let savedTheme = 'light';
    
    try {
        const storedTheme = localStorage.getItem('theme');
        // Validar que el tema guardado sea válido
        const validThemes = ['light', 'dark', 'cupcake', 'emerald', 'synthwave'];
        savedTheme = (storedTheme && validThemes.includes(storedTheme)) ? storedTheme : 'light';
    } catch (error) {
        console.warn('Error al leer el tema desde localStorage:', error);
        savedTheme = 'light';
    }
    
    const darkThemes = ['dark', 'synthwave'];
    const root = document.documentElement;
    
    // Aplicar variables CSS
    const themeColors = themeConfigs[savedTheme] || themeConfigs.light;
    Object.entries(themeColors).forEach(([key, value]) => {
        root.style.setProperty(`--color-${key}`, value);
    });
    
    // Aplicar clases
    root.setAttribute('data-theme', savedTheme);
    if (darkThemes.includes(savedTheme)) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
    
    // Aplicar color de fondo al body
    document.body.style.backgroundColor = themeColors.base;
    
    console.log('Tema inicial aplicado:', savedTheme);
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        // Convertir notación con punto a slash para la ruta del archivo
        const path = name.replace(/\./g, '/');
        return resolvePageComponent(`./Pages/${path}.vue`, import.meta.glob('./Pages/**/*.vue'));
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
