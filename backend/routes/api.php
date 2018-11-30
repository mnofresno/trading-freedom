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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('auth/login', 'Auth\LoginController@login');
Route::post('auth/register', 'Auth\LoginController@register');

Route::group(['middleware' => 'jwt.auth'], function ()
{
    Route::resource('balances', 'BalancesController');
    Route::resource('exchanges', 'ExchangesProvidersController');
});