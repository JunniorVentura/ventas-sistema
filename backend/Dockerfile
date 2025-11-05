# Imagen base con PHP 8.2 y FPM
FROM php:8.2-fpm

# Establecer directorio de trabajo
WORKDIR /var/www

# Instalar dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip unzip \
    git curl \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar todos los archivos del backend al contenedor
COPY . .

# Instalar dependencias con Composer
RUN composer install

# Exponer el puerto donde Laravel servirá la app
EXPOSE 8000

# Comando por defecto para desarrollo
CMD php artisan serve --host=0.0.0.0 --port=8000
