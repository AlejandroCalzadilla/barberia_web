import { ref, computed } from 'vue'

/**
 * Composable para búsqueda/filtrado local de arrays
 * @param {Array} items - Array de objetos a filtrar
 * @param {Array} searchFields - Campos del objeto donde buscar (ej: ['name', 'email', 'user.name'])
 * @returns {Object} - { searchQuery, filteredItems, highlightText }
 */
export function useLocalSearch(items, searchFields = []) {
  const searchQuery = ref('')

  /**
   * Obtiene el valor de una propiedad anidada (ej: 'user.name')
   */
  function getNestedValue(obj, path) {
    return path.split('.').reduce((current, key) => current?.[key], obj)
  }

  /**
   * Items filtrados según el searchQuery
   */
  const filteredItems = computed(() => {
    if (!searchQuery.value || !items.value) {
      return items.value || []
    }

    const query = searchQuery.value.toLowerCase().trim()

    return items.value.filter(item => {
      return searchFields.some(field => {
        const value = getNestedValue(item, field)
        if (!value) return false
        
        return String(value).toLowerCase().includes(query)
      })
    })
  })

  /**
   * Función helper para resaltar texto coincidente en el template
   */
  function highlightText(text, query = searchQuery.value) {
    if (!query || !text) return text

    const regex = new RegExp(`(${query})`, 'gi')
    return String(text).replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>')
  }

  /**
   * Resetear búsqueda
   */
  function clearSearch() {
    searchQuery.value = ''
  }

  return {
    searchQuery,
    filteredItems,
    highlightText,
    clearSearch
  }
}
