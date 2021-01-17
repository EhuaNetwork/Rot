<?php

use Illuminate\Support\Facades\Route;

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
  dd(1);
});

Route::get('/login','Admin\Login@index');
Route::post('/var_login','Admin\Login@var_login');

Route::group(['middleware' => 'Admin.Login'], function () {
    Route::get('/index','Admin\Admin@index');

    Route::resource('/pubkey','Admin\PubKey');//公共配置
    Route::resource('/prikey','Admin\PriKey');//私聊配置
});
