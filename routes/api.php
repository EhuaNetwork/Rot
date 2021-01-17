<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::group(['middleware' => 'VideoVerify'], function () {
//    Route::any('getlist', 'API\Search@getlist');

});
Route::any('qqrot', 'API\QQROT@run');
Route::any('task', 'API\Task@run');

Route::any('wxrot', 'API\WXROT@run');

Route::any('demo', 'API\Demo@init');
