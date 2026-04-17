<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Middlewares\PermissionMiddleware;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);

    $router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
    $router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
    $router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

    $router->get('/password/forgot', [AuthController::class, 'showForgotPassword'], [GuestMiddleware::class]);
    $router->post('/password/forgot', [AuthController::class, 'forgotPassword'], [GuestMiddleware::class]);
    $router->get('/password/reset', [AuthController::class, 'showResetPassword'], [GuestMiddleware::class]);
    $router->post('/password/reset', [AuthController::class, 'resetPassword'], [GuestMiddleware::class]);

    $router->get('/dashboard', [DashboardController::class, 'index'], [
        AuthMiddleware::class,
        new PermissionMiddleware('dashboard.view'),
    ]);
};
