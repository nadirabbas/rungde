FROM officialnadir/php-gerty:2.0

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /usr/src/app

COPY package*.json ./
RUN npm install

COPY . . 
RUN composer install

RUN npm run build


CMD php artisan serve --host=0.0.0.0 --port=80