<?php

use \SallePW\SlimApp\Middleware\StartSessionMiddleware;
use \SallePW\SlimApp\Controller\AccountController;
use \SallePW\SlimApp\Controller\HomeController;
use \SallePW\SlimApp\Controller\VisitsController;
use \SallePW\SlimApp\Controller\CookieMonsterController;
use \SallePW\SlimApp\Controller\FlashController;
use \SallePW\SlimApp\Controller\PostUserController;
use \SallePW\SlimApp\Controller\LoginController;
use \SallePW\SlimApp\Controller\LogoutController;
use \SallePW\SlimApp\Controller\RegisterController;
use \SallePW\SlimApp\Controller\ActivationController;
use \SallePW\SlimApp\Controller\ProfileController;
use \SallePW\SlimApp\Controller\TransactionsController;
use \SallePW\SlimApp\Controller\ProfileSecurityController;
use \SallePW\SlimApp\Controller\RequestController;
use \SallePW\SlimApp\Controller\SendMoneyController;

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

$app->get('/sign-in', LoginController::class . ':showLoginFormAction');
$app->post('/sign-in', LoginController::class . ':loginAction')->setName('login');

$app->get('/activate', ActivationController::class . ':showLoginFormAction');

$app->get('/sign-up', RegisterController::class . ':showRegisterFormAction');
$app->post('/sign-up', RegisterController::class . ':registerAction')->setName('register');

$app->post('/logout', LogoutController::class . ':logoutAction')->setName('logout');

$app->get('/account/summary', AccountController::class . ':showDashboard');

$app->get('/profile', ProfileController::class . ':showProfile');
$app->post('/profile', ProfileController::class . ':profileAction')->setName('profile_form');

$app->get('/profile/security', ProfileSecurityController::class . ':showProfileSecurity')->setName('security_form_form');
$app->post('/profile/security', ProfileSecurityController::class . ':profileSecurityAction')->setName('security_form');

$app->get('/account/bank-account', TransactionsController::class . ':showLoadMoney')->setName('associate-account');
$app->post('/account/bank-account', TransactionsController::class . ':associateAccountAction');
$app->post('/account/bank-account/load', TransactionsController::class . ':loadMoneyAction')->setName('load-money');

$app->get('/account/money/send', SendMoneyController::class . ':showSendMoneyForm')->setName('send-money-form');
$app->post('/account/money/send', SendMoneyController::class . ':sendMoneyAction')->setName('send-money');

$app->get('/account/transactions', TransactionsController::class . ':showTransactions');

$app->get('/account/money/requests', RequestController::class . ':showRequest')->setName('request-money-form');
$app->post('/account/money/requests', RequestController::class . ':requestAction')->setName('request-money');
$app->get('/account/money/requests/pending', RequestController::class . ':showRequestsPending');
$app->get('/account/money/requests/{id}/accept', RequestController::class . ':acceptRequest');

