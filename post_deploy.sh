#!/bin/sh

# update application cache
php artisan optimize

# start the application

php artisan queue:work &
php artisan queue:work &
php artisan queue:work &


php-fpm -D &&  nginx -g "daemon off;"