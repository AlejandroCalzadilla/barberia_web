# Componente de Búsqueda Instantánea

## Descripción
Sistema de búsqueda instantánea con atajo de teclado **Ctrl+F** para todas las vistas de tablas.

## Características
✅ Búsqueda instantánea (mientras escribes)  
✅ Atajo de teclado: **Ctrl+F** (o Cmd+F en Mac)  
✅ Limpiar con **ESC**  
✅ Filtrado local (sin peticiones al servidor)  
✅ Contador de resultados  
✅ Resaltado visual  

## Uso en una Vista

### 1. Importar componentes necesarios

```vue
<script setup>
import SearchBar from '@/Components/SearchBar.vue'
import { ref, computed } from 'vue'

const props = defineProps({
  items: Object, // Los datos desde el controlador
})

const searchQuery = ref('')

// Filtrado local
const filteredItems = computed(() => {
  if (!searchQuery.value) {
    return props.items.data || []
  }

  const query = searchQuery.value.toLowerCase().trim()
  
  return (props.items.data || []).filter(item => {
    // Define qué campos quieres buscar
    const searchableText = [
      item.id,
      item.nombre,
      item.email,
      item.descripcion,
      // ... otros campos
    ].filter(Boolean).join(' ').toLowerCase()
    
    return searchableText.includes(query)
  })
})
</script>
```

### 2. Usar SearchBar en el template

```vue
<template>
  <AppLayout>
    <div class="mb-4">
      <SearchBar 
        v-model="searchQuery" 
        placeholder="Buscar... (Ctrl+F)"
      />
    </div>

    <!-- Indicador de resultados -->
    <div v-if="searchQuery" class="mb-3 text-sm">
      {{ filteredItems.length }} resultado{{ filteredItems.length !== 1 ? 's' : '' }} 
      de {{ items.data?.length || 0 }}
    </div>

    <!-- Tabla -->
    <table>
      <tbody>
        <!-- Mensaje cuando no hay resultados -->
        <tr v-if="filteredItems.length === 0">
          <td colspan="X" class="p-4 text-center">
            {{ searchQuery ? 'No se encontraron resultados' : 'No hay datos' }}
          </td>
        </tr>
        
        <!-- Usar filteredItems en lugar de items.data -->
        <tr v-for="item in filteredItems" :key="item.id">
          <!-- ... -->
        </tr>
      </tbody>
    </table>
  </AppLayout>
</template>
```

## Vistas ya implementadas

✅ `/clientes` - Busca por: ID, nombre, email, CI, fecha  
✅ `/servicios` - Busca por: ID, nombre, descripción, precio, estado  

## Para agregar en otras vistas

Simplemente copia el patrón de Clientes o Servicios y ajusta los campos de búsqueda según tus necesidades.

### Ejemplo para Productos:

```javascript
const filteredProductos = computed(() => {
  if (!searchQuery.value) return props.productos.data || []
  
  const query = searchQuery.value.toLowerCase().trim()
  
  return (props.productos.data || []).filter(producto => {
    const searchableText = [
      producto.id_producto,
      producto.nombre,
      producto.descripcion,
      producto.precio,
      producto.stock,
      producto.categoria?.nombre // campo relacionado
    ].filter(Boolean).join(' ').toLowerCase()
    
    return searchableText.includes(query)
  })
})
```

## Atajos de Teclado

| Atajo | Acción |
|-------|--------|
| `Ctrl+F` o `Cmd+F` | Enfocar barra de búsqueda |
| `ESC` | Limpiar búsqueda y quitar foco |
| `Enter` | (No hace nada, búsqueda es instantánea) |

## Personalización

### Cambiar placeholder
```vue
<SearchBar 
  v-model="searchQuery" 
  placeholder="Tu texto personalizado aquí"
/>
```

### Autofocus al cargar
```vue
<SearchBar 
  v-model="searchQuery" 
  :autofocus="true"
/>
```

## Archivos creados

- `resources/js/Components/SearchBar.vue` - Componente de barra de búsqueda
- `resources/js/Composables/useLocalSearch.js` - Composable helper (opcional)
