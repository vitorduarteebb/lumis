<?php

declare(strict_types=1);

use App\Core\Application;
use App\Support\Log;
use Dotenv\Dotenv;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Level;

$basePath = dirname(__DIR__);

Dotenv::createImmutable($basePath)->safeLoad();

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

$sessionName = $_ENV['SESSION_NAME'] ?? 'lumis_session';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($sessionName);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$logDir = $basePath . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}
$logPath = $logDir . '/app.log';
$logger = new Logger('lumis');
$logger->pushHandler(new RotatingFileHandler($logPath, 14, Level::Debug));

Log::init($logger);

$app = new Application($basePath, $logger);

return $app;
