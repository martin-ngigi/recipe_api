<?php

use App\Http\Controllers\API\AuthControllerAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function(){
    return "pong";
});


Route::group(['prefix'=>'auth'], function(){
    Route::post('/authentication', [AuthControllerAPI::class, 'authentication']);
});