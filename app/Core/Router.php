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
            if (!$this->matchPath($route['path'], $request->path())) {
                continue;
            }

            return $this->runPipeline($request, $route['handler'], $route['middlewares']);
        }

        throw new NotFoundException('Página não encontrada.');
    }

    private function matchPath(string $routePath, string $requestPath): bool
    {
        $r = rtrim($routePath, '/') ?: '/';
        $p = rtrim($requestPath, '/') ?: '/';
        if ($r === '/' && $p === '/') {
            return true;
        }
        return $r === $p;
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
