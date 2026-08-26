FROM php:8.2-apache

WORKDIR /app
ENV APACHE_DOCUMENT_ROOT /app/public

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
	git curl unzip procps nano wget zip \
	libzip-dev libonig-dev libxml2-dev \
	libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
	libpq-dev gnupg \
	&& docker-php-ext-install \
	zip ctype fileinfo mbstring pdo pdo_mysql xml gd bcmath \
	&& apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
	apt-get install -y nodejs && \
	apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar Apache
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf && \
	sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
	a2enmod rewrite headers

# Aumentar límites PHP
RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-ram.ini && \
	echo 'max_execution_time = 300' >> /usr/local/etc/php/conf.d/docker-php-exec.ini && \
	echo 'post_max_size = 100M' >> /usr/local/etc/php/conf.d/docker-php-post.ini && \
	echo 'upload_max_filesize = 100M' >> /usr/local/etc/php/conf.d/docker-php-upload.ini

# Crear usuario vscode para DevContainer (CRÍTICO)
RUN useradd -m -s /bin/bash vscode && \
	echo "vscode ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers && \
	usermod -aG www-data vscode

# Copiar código del backend
COPY backend/ /app/

# Crear directorios necesarios
RUN mkdir -p /app/bootstrap/cache /app/storage/logs && \
	chown -R www-data:www-data /app && \
	chmod -R ug+rwx /app/storage /app/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
