import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useRoles() {
    const page = usePage()
    const user = computed(() => page.props?.auth?.user || {})
    
    const isPropietario = computed(() => user.value?.is_propietario || false)
    const isBarbero = computed(() => user.value?.is_barbero || false)
    const isCliente = computed(() => user.value?.is_cliente || false)
    
    return {
        isPropietario,
        isBarbero,
        isCliente,
        user
    }
}
