#!/bin/bash

# Si hay algún error, detener la ejecución
set -e

echo "🚀 Iniciando despliegue..."

# 0. Copiar .env.example a .env si no existe
if [ ! -f /var/www/html/.env ]; then
    echo "📝 Copiando .env.example a .env..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# 1. Sobrescribir variables sensibles desde Render (solo las que vienen de ambiente)
if [ -n "$DB_HOST" ]; then
    sed -i "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" /var/www/html/.env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" /var/www/html/.env
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" /var/www/html/.env
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" /var/www/html/.env
fi

# 2. Generar APP_KEY si está vacío
if grep -q "^APP_KEY=$" /var/www/html/.env; then
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate --force
fi

# 2.5. Crear enlace simbólico para storage (necesario para servir imágenes)
echo "🔗 Creando enlace simbólico para storage..."
php artisan storage:link --force

# 3. Correr migraciones de base de datos (solo si no se han ejecutado antes)
echo "🔄 Verificando migraciones..."
if ! php artisan migrate:status | grep -q "Ran"; then
    echo "Ejecutando migraciones..."
    php artisan migrate --force
else
    echo "Migraciones ya ejecutadas, saltando..."
fi

# 4. Caché de configuración y rutas (Recomendado para producción en Render)
echo "⚡ Optimizando Laravel..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Iniciar Apache
echo "✅ Iniciando servidor Apache..."
apache2-foreground