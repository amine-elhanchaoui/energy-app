FROM php:8.3-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    # libpng-dev is used for image processing
    libpng-dev \
    # libonig-dev is used for mbstring extension(mbstring is used for multi-byte string functions for example when we use mb_strlen())
    libonig-dev \
    # libxml2-dev is used for xml extension
    libxml2-dev \
    # zip is used for zip extension
    zip \
    # unzip is used for unzip extension
    git \
    curl

# Clear cache for minimize image size and speed up the build process
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for Laravel framework 
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite for Laravel framework exactely Routing without it the routes will not work
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Update Apache config for Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Set permissions for storing logs and cache files 
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
