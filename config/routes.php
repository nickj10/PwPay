<?php

use \SallePW\SlimApp\Controller\HomeController;
use \SallePW\SlimApp\Controller\VisitsController;
use \SallePW\SlimApp\Middleware\StartSessionMiddleware;
use \SallePW\SlimApp\Controller\CookieMonsterController;
use \SallePW\SlimApp\Controller\FlashController;
use \SallePW\SlimApp\Controller\PostUserController;
use \SallePW\SlimApp\Controller\FileController;
use \SallePW\SlimApp\Controller\LoginController;
use \SallePW\SlimApp\Controller\RegisterController;
use \SallePW\SlimApp\Controller\ValidationController;


$app->add(StartSessionMiddleware::class);

$app->get(
    '/',
    HomeController::class . ":showHomePage"
)->setName('home');

$app->get(
    '/visits',
    VisitsController::class . ":showVisits"
)->setName('visits');

$app->get(
    '/cookies',
    CookieMonsterController::class . ":showAdvice"
)->setName('cookies');

$app->get(
    '/flash',
    FlashController::class . ":addMessage"
)->setName('flash');

$app->post(
    '/users',
    PostUserController::class . ":create"
)->setName('create_user');

$app->get(
    '/files',
    FileController::class . ':showFileFormAction'
)->setName('file_form');

$app->post(
    '/files',
    FileController::class . ':uploadFileAction'
)->setName('upload');

$app->get('/sign-in', LoginController::class . ':showLoginFormAction');
$app->post('/sign-in', LoginController::class . ':loginAction')->setName('login');

$app->get('/activate-token={id}', ValidationController::class . ':showLoginFormAction');

$app->get('/sign-up', RegisterController::class . ':showRegisterFormAction');
$app->post('/sign-up', RegisterController::class . ':registerAction')->setName('register');

$app->get('/logout', LoginController::class . ':logoutAction');
