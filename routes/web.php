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
   
    //return csrf_token();
    return view('welcome');
});
//shopify auth path
Route::get('/oauth/authorize', 'shopifyController@getResponse');
Route::get('/shopify', 'shopifyController@getPermission');
Route::get( '/app_index', 'ShopifyController@appIndex' )->name( 'app_index' );
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::any('add-records', 'shopifyController@addRecords')->name('add-records');
