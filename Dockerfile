FROM officialnadir/php-gerty:2.0
RUN php -v
RUN node -v
RUN apk add nginx php8-fpm

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /user/src/app

COPY package*.json ./
RUN npm install

COPY . . 
RUN composer install

RUN npm run build

RUN cp -r /user/src/app/* /var/www/html

COPY nginx.conf /etc/nginx/nginx.conf

CMD nginx && php artisan queue:work