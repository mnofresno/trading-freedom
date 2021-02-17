<?php
use Illuminate\Support\Facades\Config;

if(!Config::get('app.debug')) {
    // This is a workarround in order to circunvent
    // a bug in laravel which makes it incompatible with PHP > 7.2
    if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
        // Ignores notices and reports all other kinds... and warnings
        error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        // error_reporting(E_ALL ^ E_WARNING); // Maybe this is enough
    }
}

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
    Route::resource('orders', 'OrdersController');
    Route::resource('exchanges/own', 'OwnExchangeProvidersController');
    Route::resource('exchanges', 'ExchangeProvidersController');
    Route::resource('apikeys', 'ApiKeysController');
    Route::resource('users/own', 'OwnUsersController');
});