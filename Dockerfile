FROM php:8.2-apache

# Installation des extensions PHP nécessaires à la base de données et aux uploads Cloudinary
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_mysql curl \
    && rm -rf /var/lib/apt/lists/*

# Activation du module rewrite d'Apache
RUN a2enmod rewrite

# Copie des fichiers du projet dans le répertoire web d'Apache
COPY . /var/www/html/

# Permissions pour Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
