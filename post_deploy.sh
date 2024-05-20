#!/bin/sh

# update application cache
php artisan optimize

# start the application

php artisan queue:work &
php artisan reverb:start --host=0.0.0.0 --port=8082 &

php-fpm -D && nginx 