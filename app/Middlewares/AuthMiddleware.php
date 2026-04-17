<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Helpers\Response;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        if (empty($_SESSION['user_id'])) {
            Response::redirect('/login');
        }

        return $next($request);
    }
}
