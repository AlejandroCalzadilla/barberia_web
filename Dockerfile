# --- Etapa 1: Construcción de Frontend (Node/Vite) ---
FROM node:20-alpine as frontend_build

WORKDIR /app

# Copiamos archivos de dependencias
COPY package*.json vite.config.js ./

# Instalamos dependencias
RUN npm install --production=false

# Copiamos código fuente
COPY resources ./resources
COPY public ./public
COPY tailwind.config.js postcss.config.js jsconfig.json ./

# Compilamos assets con Vite
RUN npm run build


# --- Etapa 2: Servidor Web + PHP (Apache) ---
FROM php:8.2-apache

# 1. Instalar dependencias del sistema necesarias para Laravel y PostgreSQL (usualmente usado en Render/Neon)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    curl

# 2. Limpiar caché para que la imagen sea ligera
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Instalar extensiones de PHP
# Nota: Agrego 'pgsql' y 'pdo_pgsql' porque vi que usas bases de datos como Neon/Postgres
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# 4. Configurar Apache para Laravel
# Activamos mod_rewrite (necesario para las rutas de Laravel)
RUN a2enmod rewrite

# Cambiamos el DocumentRoot de Apache para que apunte a /public (estándar de Laravel)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Configurar directorio de trabajo
WORKDIR /var/www/html

# 7. Copiar archivos del proyecto (excepto node_modules y public/build que ya están compilados)
COPY --chown=www-data:www-data . .

# 8. Copiar los assets compilados de Vue desde la Etapa 1
COPY --from=frontend_build --chown=www-data:www-data /app/public/build /var/www/html/public/build

# 9. Instalar dependencias de PHP (Producción)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 10. Permisos (Crucial para que Apache pueda escribir en storage)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 755 /var/www/html/storage
RUN chmod -R 755 /var/www/html/bootstrap/cache

# 11. Render asigna un puerto dinámico, pero Apache escucha en el 80 por defecto.
# Render mapeará esto automáticamente.
COPY docker-entrypoint.sh /usr/local/bin/
# Aseguramos permisos de ejecución
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

# 12. Comando de inicio (Inicia el script que genera .env y después Apache)
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]