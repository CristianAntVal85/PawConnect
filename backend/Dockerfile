FROM php:8.2-fpm

# Directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar archivos del proyecto
COPY . .

# Crear directorios necesarios
RUN mkdir -p bootstrap/cache storage && chown -R www-data:www-data /var/www/html && chmod -R 775 bootstrap/cache storage

# Instalar dependencias PHP
RUN composer install --optimize-autoloader

# Puerto por defecto
EXPOSE 8000

# Comando por defecto
CMD ["php-fpm"]
