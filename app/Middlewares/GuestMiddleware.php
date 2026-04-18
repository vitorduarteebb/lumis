<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Helpers\Response;

final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!empty($_SESSION['user_id'])) {
            if (function_exists('lumis_is_delivery_only_session') && lumis_is_delivery_only_session()) {
                Response::redirect('/locacoes/painel-entregador');
            }
            Response::redirect('/dashboard');
        }

        return $next($request);
    }
}
