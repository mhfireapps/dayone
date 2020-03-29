<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/install', 'AppController@install');
Route::get('/auth', 'AppController@auth');
Route::get('/scripts-tag', 'AppController@scriptsTag');
Route::get('/test-webhooks', 'AppController@testWebhooks');

Route::group(['prefix' => 'webhooks'], function() {
	Route::get('/all', 'WebhookController@all')->middleware('webhook');
	Route::any('/products', 'WebhookController@products')->middleware('webhook');
	Route::any('/orders', 'WebhookController@orderCreate')->middleware('webhook');
	Route::any('/orders/delete', 'WebhookController@orderDelete')->middleware('webhook');
	Route::any('/themes/updated', 'WebhookController@themeUpdated')->middleware('webhook');
	Route::any('/uninstall', 'WebhookController@uninstall')->middleware('webhook');
	Route::any('/shop/update', 'WebhookController@updateShop')->middleware('webhook');
	Route::any('/customer/create', 'WebhookController@customerCreate')->middleware('webhook');
	Route::any('/customer/delete', 'WebhookController@customerDelete')->middleware('webhook');
});