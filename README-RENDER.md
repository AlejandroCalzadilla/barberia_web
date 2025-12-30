# Barbería Web - Despliegue en Render

## 🚀 Despliegue Automático

El proyecto está configurado para desplegarse automáticamente en Render con Docker.

### Variables de Entorno en Render

Configura estas variables en tu dashboard de Render:

```
DB_HOST=tu-host-de-neon
DB_DATABASE=tu-base-de-datos
DB_USERNAME=tu-usuario
DB_PASSWORD=tu-contraseña
```

### Comandos Útiles

#### Ejecutar Seeders (solo cuando necesites datos de prueba)
```bash
# Desde tu máquina local (conectado a la BD de Render)
php artisan app:run-seeders

# O usando el script
./run-seeders.sh
```

#### Verificar estado de migraciones
```bash
php artisan migrate:status
```

#### Ejecutar migraciones manualmente (si es necesario)
```bash
php artisan migrate --force
```

## ⚠️ Importante

- **Las migraciones se ejecutan automáticamente** cada vez que Render "despierta" el servicio
- **Los seeders NO se ejecutan automáticamente** para evitar duplicados
- Si necesitas datos de prueba, ejecuta los seeders manualmente una sola vez

## 🔧 Desarrollo Local

```bash
# Instalar dependencias
composer install
npm install

# Configurar .env
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones y seeders
php artisan migrate
php artisan db:seed

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
```