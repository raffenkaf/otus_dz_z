<?php

use App\Controllers\IndexController;
use App\Controllers\UserController;
use Slim\App;

return function (App $app) {
    $app->get('/', [IndexController::class, 'index']);
    $app->get('/user/register', [UserController::class, 'register']);
    $app->post('/user/register', [UserController::class, 'register']);
    $app->get('/user/login', [UserController::class, 'login']);
    $app->post('/user/login', [UserController::class, 'login']);
    $app->get('/user/logout', [UserController::class, 'logout']);
    $app->get('/user/personal-page', [UserController::class, 'personalPage']);
    $app->get('/user/page/{id}', [UserController::class, 'page']);
    $app->post('/user/add-friend/{id}', [UserController::class, 'addFriend']);
    $app->post('/user/remove-friend/{id}', [UserController::class, 'removeFriend']);
};