FROM php:8.2-fpm

# Directorio de trabajo
WORKDIR /workspaces/pawconnect/backend

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    nodejs \
    npm \
    libpq-dev \
    libzip-dev \
    zip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    sudo \
    openssh-client \
    && docker-php-ext-install pdo pdo_mysql zip gd bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Crear usuario vscode para DevContainer
RUN useradd -m -s /bin/bash -G sudo vscode && \
    echo "vscode ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar archivos del proyecto
COPY . .

# Crear directorios necesarios y permisos
RUN mkdir -p bootstrap/cache storage && chown -R root:root /workspaces/pawconnect && chmod -R 777 bootstrap/cache storage

# Instalar dependencias PHP
RUN composer install --optimize-autoloader

# Puerto por defecto
EXPOSE 8000

# Comando por defecto (php-fpm corre como root para que funcione correctamente)
CMD ["php-fpm"]
