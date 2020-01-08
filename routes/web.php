<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//User login
Route::post('login','UsersController@authenticate');

//User Sign up
Route::post('sign_up', 'UsersController@signup');

//User logout
Route::post('logout', 'UsersController@logout');

//Check API Key
Route::post('check', 'UsersController@check');
