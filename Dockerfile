FROM officialnadir/php-gerty:2.0

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY composer.lock composer.json /var/www/

WORKDIR /var/www

COPY . . 
RUN composer install
RUN npm install
RUN npm run build

COPY --chmod=777 . /var/www

EXPOSE 9000
CMD ["php-fpm"]