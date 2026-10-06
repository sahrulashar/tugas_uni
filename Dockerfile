FROM php:8.3-apache

# Set environment
ENV DEBIAN_FRONTEND=noninteractive
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Install system dependencies dan library yang dibutuhkan ekstensi PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Konfigurasi dan install ekstensi PHP wajib CodeIgniter 4
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    zip \
    gd \
    mbstring \
    opcache

# Aktifkan Apache mod_rewrite dan headers
RUN a2enmod rewrite headers

# Salin konfigurasi VirtualHost Apache (mengarahkan DocumentRoot ke /public)
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Install Composer binary resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Salin seluruh source code project
COPY . /var/www/html

# Install dependensi PHP via Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Setup entrypoint script (menghindari issue CRLF Windows)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Setup direktori writable dan permission www-data
RUN mkdir -p /var/www/html/writable/cache \
             /var/www/html/writable/logs \
             /var/www/html/writable/session \
             /var/www/html/writable/uploads \
             /var/www/html/writable/debugbar \
    && chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 777 /var/www/html/writable

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]