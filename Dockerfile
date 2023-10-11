FROM php:7.2-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/econtract/


# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
RUN docker-php-ext-install gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD . /var/www/econtract

COPY . /var/www/econtract/

# Set working directory
WORKDIR /var/www/econtract

COPY entrypoint.sh /var/www/econtract/
COPY .env.example /var/www/econtract/.env

RUN mkdir -p /var/www/econtract/vendor

RUN mkdir -p /var/www/econtract/storage/framework /var/www/econtract/storage/framework/cache /var/www/econtract/storage/framework/sessions /var/www/econtract/storage/debugbar /var/www/econtract/storage/framework/views

RUN cd /var/www/econtract && composer install --no-scripts

RUN ["chmod", "+x", "/var/www/econtract/entrypoint.sh"]

EXPOSE 9000

CMD ["php-fpm"]

