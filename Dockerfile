FROM php:8.2-apache

# Install MySQL extension for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Enable Apache Rewrite module
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set working directory & permissions
WORKDIR /var/www/html/
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
