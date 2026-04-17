<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\NotFoundException;
use App\Middlewares\MiddlewareInterface;

final class Router
{
    /** @var list<array{method: string, path: string, handler: array{0: class-string, 1: string}, middlewares: list<MiddlewareInterface|string>}> */
    private array $routes = [];

    /**
     * @param array{0: class-string, 1: string} $handler
     * @param list<MiddlewareInterface|string> $middlewares
     */
    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     * @param list<MiddlewareInterface|string> $middlewares
     */
    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     * @param list<MiddlewareInterface|string> $middlewares
     */
    private function addRoute(string $method, string $path, array $handler, array $middlewares): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path === '' ? '/' : $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(Request $request): string
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }
            $params = $this->matchPath($route['path'], $request->path());
            if ($params === null) {
                continue;
            }

            $req = $request->withRouteParams($params);

            return $this->runPipeline($req, $route['handler'], $route['middlewares']);
        }

        throw new NotFoundException('Página não encontrada.');
    }

    /**
     * @return array<string, string>|null
     */
    private function matchPath(string $routePath, string $requestPath): ?array
    {
        $r = rtrim($routePath, '/') ?: '/';
        $p = rtrim($requestPath, '/') ?: '/';
        if ($r === '/' && $p === '/') {
            return [];
        }
        if (!str_contains($routePath, '{')) {
            return $r === $p ? [] : null;
        }

        $parts = $r === '/' ? [] : explode('/', trim($r, '/'));
        $regexParts = [];
        foreach ($parts as $part) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $m)) {
                $regexParts[] = '(?P<' . $m[1] . '>[^/]+)';
            } else {
                $regexParts[] = preg_quote($part, '#');
            }
        }
        $inner = $regexParts === [] ? '' : implode('/', $regexParts);
        $pattern = '#^/' . $inner . '$#';

        if (!preg_match($pattern, $p, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $k => $v) {
            if (is_string($k) && $k !== '') {
                $params[$k] = (string) $v;
            }
        }

        return $params;
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     * @param list<MiddlewareInterface|string> $middlewares
     */
    private function runPipeline(Request $request, array $handler, array $middlewares): string
    {
        $next = function (Request $req) use ($handler): string {
            [$class, $method] = $handler;
            $controller = new $class();
            $result = $controller->{$method}($req);
            return is_string($result) ? $result : '';
        };

        $chain = $next;
        foreach (array_reverse($middlewares) as $middleware) {
            $mw = $this->resolveMiddleware($middleware);
            $chain = function (Request $req) use ($mw, $chain): mixed {
                return $mw->handle($req, $chain);
            };
        }

        $out = $chain($request);
        return is_string($out) ? $out : '';
    }

    private function resolveMiddleware(MiddlewareInterface|string $middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }
        return new $middleware();
    }
}
