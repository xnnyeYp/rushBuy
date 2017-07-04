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

$app->get('/', 'IndexController@Index');

$app->post('users/login', 'UserController@login');
$app->post('users/register', 'UserController@register');

$app->group(['middleware' => 'auth'], function () use ($app) {
    $app->get('users/info', 'UserController@info');
});

