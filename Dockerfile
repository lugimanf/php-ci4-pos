# Menggunakan PHP dengan Apache sebagai basis image
FROM php:8.2-apache

# Aktifkan mod_rewrite untuk Apache
RUN a2enmod rewrite

# Install dependencies untuk Composer dan ekstensi PHP yang diperlukan
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP yang dibutuhkan
RUN  docker-php-ext-install intl mysqli pdo pdo_mysql \
&& docker-php-ext-install gd \
&& docker-php-ext-install zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy custom Apache config
COPY apache.conf /etc/apache2/conf-available/custom.conf

# Set LogLevel Apache ke debug
RUN echo "LogLevel debug" >> /etc/apache2/apache2.conf

# Enable custom config
RUN a2enconf custom

# Set DocumentRoot ke /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Set permissions untuk direktori project
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html