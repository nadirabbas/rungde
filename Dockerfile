FROM officialnadir/php-gerty:2.0
RUN apk add nginx

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /user/src/app

COPY package*.json ./
RUN npm install

COPY . . 
RUN composer install

RUN npm run build

RUN cp -r /user/src/app/* /var/www/html

COPY nginx.conf /etc/nginx/nginx.conf

CMD nginx