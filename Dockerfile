# ==============================================================================
# Dockerfile para Laravel 13 + Inertia + Vue 3 optimizado para Coolify (v4.3+)
# ==============================================================================

# ------------------------------------------------------------------------------
# Fase 1: Compilación de Frontend (Vite, Tailwind v4, Vue 3)
# ------------------------------------------------------------------------------
FROM node:22-alpine AS frontend-builder
WORKDIR /app

# Instalar dependencias de Node
COPY package.json package-lock.json ./
RUN npm ci

# Copiar código fuente y compilar assets de producción
COPY . .
RUN npm run build

# ------------------------------------------------------------------------------
# Fase 2: Instalación de dependencias PHP con Composer
# ------------------------------------------------------------------------------
FROM composer:2 AS composer-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ------------------------------------------------------------------------------
# Fase 3: Imagen de producción (PHP 8.4-FPM + Nginx + Supervisord)
# ------------------------------------------------------------------------------
FROM php:8.4-fpm-alpine AS runner

# Instalar paquetes base del sistema y servidores
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    sqlite \
    libpq

# Instalar extensiones PHP necesarias para Laravel de forma limpia y ligera
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    zip

WORKDIR /var/www/html

# Copiar código fuente de la aplicación
COPY . .

# Copiar dependencias de composer y assets compilados del frontend
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=frontend-builder /app/public/build ./public/build

# Copiar archivos de configuración para Nginx, Supervisord, PHP y Entrypoint
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-production.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Crear estructura requerida y asignar permisos para www-data
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/html/storage/framework/sessions \
                /var/www/html/storage/framework/views \
                /var/www/html/storage/framework/cache \
                /var/www/html/storage/logs \
                /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Puerto expuesto para el proxy inverso de Coolify (Traefik / Caddy)
EXPOSE 80

# Healthcheck nativo de Laravel (/up)
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -f http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
