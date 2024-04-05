<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

Route::get('/', function () {
    return view('app');
})
->name('application');


Route::post('/urls', [UrlController::class, 'store']);
Route::get('/{shortUrl}', [UrlController::class, 'redirect'])->where('shortUrl', '[A-Za-z0-9]{6}');
Route::get('/{folder}/{shortUrl}', [UrlController::class, 'redirectWithFolder'])->where('shortUrl', '[A-Za-z0-9]{6}');