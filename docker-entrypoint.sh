#!/bin/bash

# Si hay algún error, detener la ejecución
set -e

echo "🚀 Iniciando despliegue..."

# 1. Correr migraciones de base de datos
echo "🔄 Ejecutando migraciones..."
php artisan migrate --force

# 2. Caché de configuración y rutas (Recomendado para producción en Render)
echo "⚡ Optimizando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Iniciar Apache (ESTO ES LO QUE HACE QUE TODO SIGA FUNCIONANDO IGUAL)
echo "✅ Iniciando servidor Apache..."
apache2-foreground