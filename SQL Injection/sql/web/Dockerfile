# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Install mysqli extension for MySQL database connection
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite (optional, good for routing in PHP apps)
RUN a2enmod rewrite

# Copy custom Apache config if needed (optional)
# COPY apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# Set working directory to /var/www/html (already default)
WORKDIR /var/www/html

# Copy all project files into the container
COPY . /var/www/html
