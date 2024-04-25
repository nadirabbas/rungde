FROM officialnadir/php-gerty:2.0
RUN apk add nginx

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN chmod -R 777 /var/www/html
WORKDIR /var/www/html

COPY package*.json ./
RUN npm install

COPY . . 
RUN composer install

RUN npm run build

COPY nginx.conf /etc/nginx/nginx.conf

CMD nginx