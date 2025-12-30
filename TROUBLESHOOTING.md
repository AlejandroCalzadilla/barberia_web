# 🐛 Solución de Problemas - Barbería Web en Render

## Problema: Imágenes retornan 404

**Síntomas:**
- Las imágenes cargan bien localmente pero no en Render
- URLs como `https://barberia-web-mq2p.onrender.com/storage/servicios/servicio_2.jpg` retornan 404

**Causas posibles:**
1. El enlace simbólico `/public/storage` no se crea correctamente
2. Los permisos de la carpeta `/storage/app/public` no son accesibles

**Soluciones implementadas:**
1. Ruta personalizada en `routes/web.php` que sirve archivos desde storage directamente:
   ```
   GET /storage/{path} -> storage_path('app/public/{path}')
   ```

2. Mejorado el script `docker-entrypoint.sh` para:
   - Eliminar links simbólicos viejos
   - Crear uno nuevo con `--force`
   - Asegurar permisos correctos (755)

3. Actualizado el Dockerfile para establecer permisos correctos en tiempo de construcción

## Problema: No se pueden editar/crear servicios

**Síntomas:**
- La página de editar servicio no carga o la petición falla
- Errores de validación o permisos al guardar

**Revisar:**
1. **Logs de Render:**
   - Ve a tu proyecto en Render
   - Ve a "Logs" en el panel
   - Busca errores PHP o errores HTTP

2. **Base de datos:**
   - Asegúrate de que la tabla `servicios` existe
   - Verifica que tienes datos de prueba: `php artisan app:run-seeders`

3. **Permisos:**
   - La carpeta `/storage/app/public` debe ser escribible por `www-data`
   - Ya está configurado en el Dockerfile y docker-entrypoint.sh

## Pasos para diagnosticar

### Local
```bash
# 1. Verificar que todo esté en orden
php artisan migrate:status
php artisan storage:link

# 2. Crear datos de prueba
php artisan app:run-seeders

# 3. Verificar permisos
ls -la storage/app/public/
# Debe mostrar permisos como: drwxr-xr-x (755)

# 4. Verificar que se creó el link simbólico
ls -la public/storage
# Debe apuntar a ../storage/app/public
```

### En Render
```bash
# Acceder a la consola de Render
# Ve a tu proyecto → Shell

# Verificar estructura
ls -la /var/www/html/storage/app/public/
ls -la /var/www/html/public/storage

# Ver logs
tail -f /var/www/html/storage/logs/laravel.log

# Verificar BD
php artisan tinker
>>> \App\Models\Servicio::count()
>>> exit()
```

## Forzar reconstrucción en Render

Si aún hay problemas después de los cambios:

1. **Opción 1:** Hacer un nuevo commit
   ```bash
   git add .
   git commit -m "Fix: Mejorar manejo de imágenes en storage"
   git push origin main
   ```

2. **Opción 2:** En Render Dashboard
   - Proyecto → Manual Deploy → Deploy latest commit

## URL de prueba para imágenes

Después de ejecutar seeders:
```
https://barberia-web-mq2p.onrender.com/storage/servicios/servicio_0.jpg
https://barberia-web-mq2p.onrender.com/storage/productos/producto_0.jpg
```

Deben cargar sin errores 404.

## Contacto/Siguiente paso

Si aún tienes problemas:
1. Comparte los logs de Render (en la sección de Logs)
2. Verifica que ejecutaste `php artisan app:run-seeders` para crear datos de prueba
3. Revisa que la BD en Render está correctamente configurada