#!/bin/bash
set -e

echo "==> Inicializando contenedor Laravel para Coolify..."

# 1. Crear y asegurar base de datos SQLite si está configurada
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_PATH")"
    if [ ! -f "$DB_PATH" ]; then
        echo "==> Creando archivo SQLite en $DB_PATH..."
        touch "$DB_PATH"
    fi
    chown -R www-data:www-data "$(dirname "$DB_PATH")"
    chmod 775 "$(dirname "$DB_PATH")"
    chmod 664 "$DB_PATH"
fi

# 2. Permisos para storage y bootstrap/cache
echo "==> Configurando permisos de storage y cache..."
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Enlace simbólico de storage público
if [ ! -L /var/www/html/public/storage ]; then
    echo "==> Creando storage:link..."
    php artisan storage:link --force || true
fi

# 4. Migraciones automáticas opcionales
if [ "${RUN_MIGRATIONS:-false}" = "true" ] || [ "${AUTORUN_ENABLED:-false}" = "true" ]; then
    echo "==> Ejecutando migraciones de base de datos..."
    php artisan migrate --force
fi

# 5. Seeder opcional
if [ "${RUN_SEEDER:-false}" = "true" ]; then
    echo "==> Ejecutando database seeder..."
    php artisan db:seed --force
fi

# 6. Optimización de caches para producción
if [ "${APP_ENV:-production}" = "production" ] && [ -n "$APP_KEY" ]; then
    echo "==> Optimizando caches de Laravel (config, routes, views)..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "==> Inicialización completada. Iniciando Nginx y PHP-FPM..."
exec "$@"
