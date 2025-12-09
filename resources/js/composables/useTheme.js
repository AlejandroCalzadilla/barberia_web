import { ref, watch, onMounted } from 'vue'

// Lista de temas disponibles con colores CSS personalizados
const availableThemes = [
    { 
        value: 'light', 
        name: '☀️ Claro', 
        icon: '☀️',
        colors: {
            primary: '#3b82f6',     // blue-500
            secondary: '#8b5cf6',   // violet-500
            accent: '#06b6d4',      // cyan-500
            neutral: '#374151',     // gray-700
            base: '#ffffff',        // white
            info: '#0ea5e9',        // sky-500
            success: '#22c55e',     // green-500
            warning: '#f59e0b',     // amber-500
            error: '#ef4444',       // red-500
            sidebarBg: '#ecf1f5ff',   // custom for sidebar background
        }
    },
    { 
        value: 'dark', 
        name: '🌙 Oscuro', 
        icon: '🌙',
        colors: {
            primary: '#60a5fa',     // blue-400
            secondary: '#a78bfa',   // violet-400
            accent: '#22d3ee',      // cyan-400
            neutral: '#e5e7eb',     // gray-200
            base: '#1f2937',        // gray-800
            info: '#38bdf8',        // sky-400
            success: '#4ade80',     // green-400
            warning: '#fbbf24',     // amber-400
            error: '#f87171',       // red-400
            sidebarBg: '#1f2945',   // custom for sidebar background
        }
    },
    { 
        value: 'cupcake', 
        name: '🧁 Cupcake', 
        icon: '🧁',
        colors: {
            primary: '#7cec48ff',     // pink-500
            secondary: '#f97316',   // orange-500
            accent: '#06b6d4',      // cyan-500
            neutral: '#64748b',     // slate-500
            base: '#fef7f0',        // orange-50
            info: '#0ea5e9',        // sky-500
            success: '#22c55e',     // green-500
            warning: '#f59e0b',     // amber-500
            error: '#ef4444',       // red-500
            sidebarBg: '#fff1f5',   // custom for sidebar background
        }
    },
    { 
        value: 'emerald', 
        name: '💚 Esmeralda', 
        icon: '💚',
        colors: {
            primary: '#10b981',     // emerald-500
            secondary: '#059669',   // emerald-600
            accent: '#06b6d4',      // cyan-500
            neutral: '#374151',     // gray-700
            base: '#ecfdf5',        // green-50
            info: '#0ea5e9',        // sky-500
            success: '#22c55e',     // green-500
            warning: '#f59e0b',     // amber-500
            error: '#ef4444',       // red-500
            sidebarBg : '#059669'
        }
    },
    { 
        value: 'synthwave', 
        name: '🌆 Synthwave', 
        icon: '🌆',
        colors: {
            primary: '#e879f9',     // fuchsia-400
            secondary: '#a855f7',   // purple-500
            accent: '#06b6d4',      // cyan-500
            neutral: '#f3e8ff',     // violet-50
            base: '#1e1b4b',        // indigo-900
            info: '#8b5cf6',        // violet-500
            success: '#22c55e',     // green-500
            warning: '#f59e0b',     // amber-500
            error: '#ef4444',       // red-500
            sidebarBg: '#a855f7', 
        }
    },
    { 
        value: 'kids', 
        name: '🚀 Niños', 
        icon: '🚀',
        colors: {
            primary: '#ff6b6b',     // rojo brillante
            secondary: '#4ecdc4',   // turquesa
            accent: '#46bacfff',      // amarillo alegre
            neutral: '#2d3436',     // gris oscuro
            base: '#fff9e6',        // amarillo muy claro
            info: '#74b9ff',        // azul claro
            success: '#0c4d3aff',     // verde menta
            warning: '#168593ff',     // amarillo pastel
            error: '#ff7675',       // rojo suave
            sidebarBg: '#fffbeb',   // turquesa
        },
        hasVideoBackground: true,
        videoUrl: '/ninos-bg.mov',
        hasCustomPatterns: true
    },
]

// Estado global del tema
const theme = ref('light')
const isInitialized = ref(false)

// Función para obtener el tema guardado en localStorage
const getStoredTheme = () => {
    if (typeof window !== 'undefined') {
        try {
            const stored = localStorage.getItem('theme') || 'light'
            // Verificar que el tema guardado esté en la lista de disponibles
            const isValidTheme = availableThemes.some(t => t.value === stored)
            return isValidTheme ? stored : 'light'
        } catch (error) {
            console.warn('Error al leer el tema desde localStorage:', error)
            return 'light'
        }
    }
    return 'light'
}

// Función para aplicar el tema al DOM y guardarlo en localStorage
const applyTheme = (newTheme) => {
    if (typeof document !== 'undefined') {
        const themeConfig = availableThemes.find(t => t.value === newTheme)
        if (!themeConfig) return
        
        try {
            // Aplicar variables CSS personalizadas al root
            const root = document.documentElement
            
            Object.entries(themeConfig.colors).forEach(([key, value]) => {
                root.style.setProperty(`--color-${key}`, value)
            })
            
            // Aplicar clase dark para Tailwind si es un tema oscuro
            const darkThemes = ['dark', 'synthwave']
            if (darkThemes.includes(newTheme)) {
                root.classList.add('dark')
            } else {
                root.classList.remove('dark')
            }
            
            // Aplicar clase del tema
            root.setAttribute('data-theme', newTheme)
            
            // Manejar video de fondo para tema de niños
            let videoElement = document.getElementById('theme-video-background')
            
            if (themeConfig.hasVideoBackground && themeConfig.videoUrl) {
                // Crear o actualizar el video de fondo
                if (!videoElement) {
                    videoElement = document.createElement('video')
                    videoElement.id = 'theme-video-background'
                    videoElement.autoplay = true
                    videoElement.loop = true
                    videoElement.muted = true
                    videoElement.playsInline = true
                    videoElement.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100vw;
                        height: 100vh;
                        object-fit: cover;
                        z-index: -1;
                        pointer-events: none;
                    `
                    document.body.insertBefore(videoElement, document.body.firstChild)
                }
                videoElement.src = themeConfig.videoUrl
                videoElement.style.display = 'block'
                document.body.style.backgroundColor = 'transparent'
            } else {
                // Remover video si existe
                if (videoElement) {
                    videoElement.style.display = 'none'
                }
                document.body.style.backgroundColor = themeConfig.colors.base
            }
            
            // Guardar en localStorage
            localStorage.setItem('theme', newTheme)
            theme.value = newTheme
            
            console.log('Tema aplicado:', newTheme)
            console.log('Colores:', themeConfig.colors)
        } catch (error) {
            console.error('Error al aplicar el tema:', error)
        }
    }
}

// Inicializar el tema solo una vez
const initializeTheme = () => {
    if (!isInitialized.value && typeof window !== 'undefined') {
        const storedTheme = getStoredTheme()
        theme.value = storedTheme
        applyTheme(storedTheme)
        isInitialized.value = true
        console.log('Tema inicializado:', storedTheme)
    }
}

// Observar cambios en el tema
watch(theme, (newTheme) => {
    if (isInitialized.value) {
        applyTheme(newTheme)
    }
})

export function useTheme() {
    // Inicializar en el primer uso
    onMounted(() => {
        initializeTheme()
    })
    
    // Si ya estamos en el navegador, inicializar inmediatamente
    if (typeof window !== 'undefined' && !isInitialized.value) {
        initializeTheme()
    }

    const toggleTheme = () => {
        const currentIndex = availableThemes.findIndex(t => t.value === theme.value)
        const nextIndex = (currentIndex + 1) % availableThemes.length
        const newTheme = availableThemes[nextIndex].value
        theme.value = newTheme
        console.log('Toggleando tema a:', newTheme)
    }

    const setTheme = (newTheme) => {
        const isValidTheme = availableThemes.some(t => t.value === newTheme)
        if (isValidTheme) {
            theme.value = newTheme
        }
    }

    const getCurrentThemeInfo = () => {
        return availableThemes.find(t => t.value === theme.value) || availableThemes[0]
    }

    const getThemeColors = () => {
        const currentTheme = getCurrentThemeInfo()
        return currentTheme.colors
    }

    return {
        theme: theme,
        availableThemes,
        toggleTheme,
        setTheme,
        getCurrentThemeInfo,
        getThemeColors,
        isInitialized
    }
}