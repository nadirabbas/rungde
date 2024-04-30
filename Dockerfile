FROM officialnadir/php-gerty:2.0

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY composer.lock composer.json /var/www/

WORKDIR /var/www

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

COPY . . 
RUN composer install
RUN npm install
RUN npm run build

COPY --chown=www:www . /var/www

USER www

EXPOSE 9000
CMD ["php-fpm"]