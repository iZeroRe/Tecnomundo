# Usamos la imagen oficial de PHP 8.1 con Apache como base
FROM php:8.1-apache

# Instalamos las extensiones de PHP para MySQL (mysqli y pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli