# Use the official PHP image with Apache
FROM php:8.1-apache

# Install MySQL
RUN docker-php-ext-install mysqli

# Install GD library for image processing
RUN apt-get update && apt-get install -y libgd-dev && docker-php-ext-install gd

# Enable Apache mod_rewrite (if needed for your application)
RUN a2enmod rewrite
