# Use the official PHP image with Apache
FROM php:8.1-apache

# Install the PDO and MySQL extensions for PHP
#RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite (if needed for your application)
RUN a2enmod rewrite
