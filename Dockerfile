FROM php:8.2-fpm-alpine
FROM node:20.11.0-alpine

RUN apk add nginx

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY package*.json ./
RUN yarn install


COPY composer.* ./
RUN composer install

COPY . . 
RUN yarn build

COPY nginx.conf /etc/nginx/nginx.conf

CMD nginx