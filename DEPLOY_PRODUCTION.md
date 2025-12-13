# Guía para Despliegue en Producción

## Problemas Corregidos

1. ✅ **app.blade.php**: Eliminada la referencia dinámica al componente Vue en `@vite`
2. ✅ **public/hot**: Eliminado el archivo que forzaba el modo desarrollo

## Pasos para Despliegue en Hosting Compartido

### 1. Configurar Variables de Entorno

Asegúrate de tener en tu `.env` en producción:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://link.cnva.mx

VITE_APP_NAME="ConectLink"
```

### 2. Compilar Assets para Producción

**En tu máquina local o servidor de build:**

```bash
# Instalar dependencias (si es necesario)
npm install

# Compilar assets para producción
npm run build

# O si necesitas SSR:
npm run build:ssr
```

### 3. Subir Archivos al Hosting

**Archivos y directorios necesarios:**

- ✅ Todo el contenido de `public/` (incluyendo la carpeta `build/`)
- ✅ Todo el código PHP del proyecto (app, config, routes, etc.)
- ✅ `.env` con las configuraciones de producción
- ✅ `vendor/` (o ejecutar `composer install --no-dev --optimize-autoloader` en el servidor)

**Archivos que NO debes subir:**
- ❌ `node_modules/`
- ❌ `resources/js/` y `resources/css/` (ya compilados en `public/build/`)
- ❌ `tests/`
- ❌ `.git/`

### 4. Generar Iconos del Manifest (Opcional pero Recomendado)

Los iconos del PWA están referenciados en `public/manifest.json` pero pueden no existir.
Necesitas crear los siguientes iconos en `public/icons/`:

- `icon-72x72.png`
- `icon-96x96.png`
- `icon-128x128.png`
- `icon-144x144.png`
- `icon-152x152.png`
- `icon-192x192.png`
- `icon-384x384.png`
- `icon-512x512.png`

**Opciones para generarlos:**
1. Usar una herramienta online como [PWA Asset Generator](https://github.com/onderceylan/pwa-asset-generator)
2. Crearlos manualmente desde `public/apple-touch-icon.png` (redimensionar a los tamaños requeridos)
3. Modificar `public/manifest.json` para usar solo los iconos que tengas disponibles

### 5. Verificar Permisos en el Servidor

```bash
# En el servidor, asegura los permisos correctos:
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Ejecutar Comandos de Laravel en el Servidor

Si tienes acceso SSH:

```bash
# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Si usas migraciones
php artisan migrate --force

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
```

### 7. Verificar que no Existe `public/hot`

El archivo `public/hot` hace que Laravel piense que Vite está en modo desarrollo.
**Asegúrate de que NO exista este archivo en producción.**

Si existe, elimínalo:
```bash
rm public/hot
```

## Verificación Post-Despliegue

1. ✅ Verifica que la aplicación carga correctamente
2. ✅ Revisa la consola del navegador (F12) - NO deberían aparecer errores de CORS
3. ✅ Verifica que los assets se cargan desde `/build/assets/` no desde `127.0.0.1:5173`
4. ✅ Comprueba que el manifest.json carga correctamente

## Solución Rápida para los Iconos

Si los iconos no existen y quieres una solución temporal, modifica `public/manifest.json` para usar solo el `apple-touch-icon.png`:

```json
{
  "icons": [
    {
      "src": "/apple-touch-icon.png",
      "sizes": "180x180",
      "type": "image/png"
    }
  ]
}
```

Luego regenera los iconos cuando tengas tiempo.
