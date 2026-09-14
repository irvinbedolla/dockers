FROM php:8.3-fpm

# Instalar dependencias del sistema y extensiones de PHP necesarias para Laravel
# libjpeg / libwebp / libfreetype son para GD: sin ellas se compila solo con PNG
# y las fotos de perfil no se pueden ni leer (JPG) ni escribir (WebP).
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP (incluyendo pdo_mysql para la base de datos)
# docker-php-ext-configure va ANTES de install: en PHP 8 el soporte de JPEG y
# WebP esta apagado por defecto y solo se enciende aqui.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# La imagen base no copia ningun php.ini, asi que upload_max_filesize se queda
# en los 2M de fabrica y una foto de celular llega vacia sin marcar error.
RUN { \
      echo 'upload_max_filesize=8M'; \
      echo 'post_max_size=12M'; \
    } > /usr/local/etc/php/conf.d/subidas.ini

# Obtener Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar el directorio de trabajo
WORKDIR /var/www

EXPOSE 9000

# Asegurar permisos correctos en carpetas de Laravel para el usuario www-data
RUN mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/framework/cache/data \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/app/public/usuarios \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage \
    && chown -R www-data:www-data /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

CMD ["php-fpm"]
