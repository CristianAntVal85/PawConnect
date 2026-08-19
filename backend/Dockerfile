FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    procps \
    nano \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Directorio de trabajo REAL para Laravel
WORKDIR /var/www/html

# Copiar archivos del backend (context es ./backend)
COPY . /var/www/html/

# Crear directorios necesarios antes de composer
RUN mkdir -p /var/www/html/bootstrap/cache /var/www/html/storage

# Instalar dependencias PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto
EXPOSE 8000

# Servir Laravel con artisan serve
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
