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
	Route::post('/products', 'WebhookController@products')->middleware('webhook');
	Route::post('/orders', 'WebhookController@orders')->middleware('webhook');
	Route::post('/themes/updated', 'WebhookController@themeUpdated')->middleware('webhook');
	Route::post('/uninstall', 'WebhookController@uninstall')->middleware('webhook');
	Route::post('/shop', 'WebhookController@shop')->middleware('webhook');
});