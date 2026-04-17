<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;

/**
 * Placeholder para rate limiting (Redis, arquivo, etc.) na Fase de hardening.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): mixed
    {
        return $next($request);
    }
}
