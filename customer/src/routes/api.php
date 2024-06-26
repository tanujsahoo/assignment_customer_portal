<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('customer/login', 'App\Http\Controllers\CustomerController@login');
Route::post('customer/update-personal-details', 'App\Http\Controllers\CustomerController@updateDetails');


Route::post('orders', 'App\Http\Controllers\OrderController@store');
Route::patch('orders/{id}', 'App\Http\Controllers\OrderController@assignOrder')->where('id', '[1-9][0-9]*');
Route::get('orders', 'App\Http\Controllers\OrderController@listOrders');