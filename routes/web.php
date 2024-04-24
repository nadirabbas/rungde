<?php

use Illuminate\Support\Facades\Route;

Route::any('/{any}', fn () => view('index'))->where('any', '^(?!api).*$');
