<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/product-image/{filename}', function ($filename) {
    $path = storage_path('app/public/products/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    return response()->file($path);
});
