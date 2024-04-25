FROM officialnadir/php-gerty:2.0
RUN apk add nginx php8-fpm

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /usr/src/app

COPY package*.json ./
RUN npm install

COPY . . 
RUN composer install

RUN npm run build

COPY nginx.conf /etc/nginx/nginx.conf

CMD nginx && php-fpm