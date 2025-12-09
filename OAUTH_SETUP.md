# Configuración de OAuth con Laravel Socialite

Este proyecto soporta autenticación OAuth con Google, Facebook y GitHub.

## Configuración

### 1. Variables de Entorno

Agrega las siguientes variables a tu archivo `.env`:

```env
# Google OAuth
GOOGLE_CLIENT_ID=tu-client-id-de-google
GOOGLE_CLIENT_SECRET=tu-client-secret-de-google
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=tu-app-id-de-facebook
FACEBOOK_CLIENT_SECRET=tu-app-secret-de-facebook
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback

# GitHub OAuth
GITHUB_CLIENT_ID=tu-client-id-de-github
GITHUB_CLIENT_SECRET=tu-client-secret-de-github
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

### 2. Obtener Credenciales OAuth

#### Google
1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita la API de Google+
4. Ve a "Credenciales" > "Crear credenciales" > "ID de cliente de OAuth 2.0"
5. Configura la pantalla de consentimiento
6. Tipo de aplicación: "Aplicación web"
7. Agrega las URIs de redirección autorizadas:
   - `http://localhost:8000/auth/google/callback`
   - `https://tu-dominio.com/auth/google/callback` (producción)
8. Copia el Client ID y Client Secret

#### Facebook
1. Ve a [Facebook for Developers](https://developers.facebook.com/)
2. Crea una nueva app o selecciona una existente
3. Agrega el producto "Facebook Login"
4. En la configuración de Facebook Login:
   - URIs de redirección OAuth válidas: `http://localhost:8000/auth/facebook/callback`
5. En "Configuración básica":
   - Copia el "ID de la app" (Client ID)
   - Muestra y copia el "App Secret" (Client Secret)

#### GitHub
1. Ve a [GitHub Developer Settings](https://github.com/settings/developers)
2. Click en "New OAuth App"
3. Completa el formulario:
   - Application name: Nombre de tu app
   - Homepage URL: `http://localhost:8000`
   - Authorization callback URL: `http://localhost:8000/auth/github/callback`
4. Click en "Register application"
5. Copia el Client ID
6. Genera y copia un nuevo Client Secret

### 3. Migración de Base de Datos

Las columnas `provider` y `provider_id` ya fueron agregadas a la tabla `users` mediante la migración:

```bash
php artisan migrate
```

### 4. Flujo de Autenticación

Cuando un usuario se registra/inicia sesión con OAuth:

1. Hace clic en uno de los botones de OAuth (Google, Facebook, GitHub)
2. Es redirigido al proveedor para autenticarse
3. Después de autorizar, regresa a la aplicación
4. El sistema:
   - Busca si existe un usuario con ese email
   - Si existe: actualiza provider/provider_id y hace login
   - Si no existe: crea nuevo User + Cliente y hace login
   - Redirige según el rol (clientes → catálogo, barberos/admin → dashboard)

### 5. Usuarios Creados por OAuth

Los usuarios creados mediante OAuth:
- Tienen `email_verified_at` automáticamente establecido
- Tienen un password aleatorio (pueden establecer uno después)
- Se les asigna automáticamente el rol de Cliente
- Pueden vincular su cuenta con múltiples proveedores

### 6. Seguridad

⚠️ **IMPORTANTE**: 
- Nunca compartas tus Client Secrets
- Nunca los subas a repositorios públicos
- Usa diferentes credenciales para desarrollo y producción
- En producción, actualiza las URIs de redirección

### 7. Testing

Para probar OAuth en desarrollo local:

1. Asegúrate de que tu app esté corriendo en `http://localhost:8000`
2. Las URIs de redirección deben coincidir exactamente
3. Algunos proveedores (como Facebook) requieren HTTPS en producción

### 8. Auditoría de Seguridad

Se detectó 1 vulnerabilidad durante la instalación. Ejecuta:

```bash
composer audit
```

Para revisar y actualizar dependencias vulnerables.

## Rutas OAuth

- Redirect: `GET /auth/{provider}` (google|facebook|github)
- Callback: `GET /auth/{provider}/callback`

## Archivos Modificados

- `app/Http/Controllers/SocialAuthController.php` - Lógica OAuth
- `app/Models/User.php` - Campos provider/provider_id
- `config/services.php` - Configuración de proveedores
- `routes/web.php` - Rutas OAuth
- `resources/js/Pages/Auth/Login.vue` - Botones OAuth
- `resources/js/Pages/Auth/Register.vue` - Botones OAuth
- `database/migrations/2025_11_21_223834_add_oauth_columns_to_users_table.php` - Columnas DB
