<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'Lumis ERP',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo',
];
