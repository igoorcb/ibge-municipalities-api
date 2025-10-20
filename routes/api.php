<?php

use App\Http\Controllers\MunicipalityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/municipalities/{uf}', [MunicipalityController::class, 'index']);
