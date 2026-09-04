FROM php:8.3-apache

# Install dependency dan PHP extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    intl \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Aktifkan Apache mod_rewrite
RUN a2enmod rewrite

# Arahkan Apache ke folder public CodeIgniter
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project
COPY . /var/www/html

# Install dependency
RUN composer install --no-interaction --prefer-dist -vvv

# Permission writable CodeIgniter
RUN chown -R www-data:www-data /var/www/html/writable

EXPOSE 80