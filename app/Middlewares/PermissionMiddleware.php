<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Exceptions\HttpException;

/**
 * Verifica se a permissão está presente em $_SESSION['permissions'] (array de strings).
 * Instancie com new PermissionMiddleware('modulo.acao') nas rotas.
 */
final class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $permission
    ) {
    }

    public function handle(Request $request, callable $next): mixed
    {
        $roles = $_SESSION['role_slugs'] ?? [];
        if (is_array($roles) && in_array('master', $roles, true)) {
            return $next($request);
        }

        $list = $_SESSION['permissions'] ?? [];
        if (!is_array($list)) {
            $list = [];
        }

        if (!in_array($this->permission, $list, true)) {
            throw new HttpException(403, 'Acesso negado.');
        }

        return $next($request);
    }
}
