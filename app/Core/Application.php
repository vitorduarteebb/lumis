<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;
use App\Exceptions\NotFoundException;
use Monolog\Logger;
use Throwable;

final class Application
{
    private Router $router;

    public function __construct(
        private readonly string $basePath,
        private readonly Logger $logger
    ) {
        date_default_timezone_set(config('app.timezone') ?? 'America/Sao_Paulo');

        $this->router = new Router();
        $routes = require base_path('routes/web.php');
        $routes($this->router);
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function run(): void
    {
        $request = Request::capture();

        try {
            $response = $this->router->dispatch($request);
            if (is_string($response)) {
                echo $response;
            }
        } catch (NotFoundException $e) {
            http_response_code(404);
            echo View::render('errors/404', [
                'title' => 'Não encontrado',
                'pageTitle' => 'Página não encontrada',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Erro 404', 'href' => null],
                ],
                'message' => $e->getMessage(),
            ], 'layouts/main');
        } catch (HttpException $e) {
            http_response_code($e->getStatusCode());
            echo View::render('errors/http', [
                'title' => 'Acesso negado',
                'pageTitle' => 'Acesso negado',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Erro ' . $e->getStatusCode(), 'href' => null],
                ],
                'code' => $e->getStatusCode(),
                'message' => $e->getMessage(),
            ], 'layouts/main');
        } catch (Throwable $e) {
            try {
                $this->logger->error($e->getMessage(), ['exception' => $e]);
            } catch (Throwable $logErr) {
                error_log('[Lumis] Falha ao gravar log: ' . $logErr->getMessage());
                error_log('[Lumis] Exceção original: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
            }
            $debug = (bool) config('app.debug');
            http_response_code(500);
            if ($debug) {
                echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
            } else {
                echo View::render('errors/500', [
                    'title' => 'Erro interno',
                    'pageTitle' => 'Erro interno',
                    'breadcrumbs' => [
                        ['label' => 'Início', 'href' => '/dashboard'],
                        ['label' => 'Erro 500', 'href' => null],
                    ],
                ], 'layouts/main');
            }
        }
    }
}
