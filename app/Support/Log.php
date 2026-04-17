<?php

declare(strict_types=1);

namespace App\Support;

use Monolog\Logger;

final class Log
{
    private static ?Logger $logger = null;

    public static function init(Logger $logger): void
    {
        self::$logger = $logger;
    }

    public static function instance(): Logger
    {
        if (self::$logger === null) {
            throw new \RuntimeException('Logger não inicializado.');
        }
        return self::$logger;
    }
}
