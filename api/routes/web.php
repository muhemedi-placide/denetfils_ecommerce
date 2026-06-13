<?php

use App\Http\Controllers\Api\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);

Route::get('/', function () {
    return view('welcome');
});
