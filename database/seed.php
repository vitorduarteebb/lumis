<?php

declare(strict_types=1);

/**
 * Popula dados iniciais (empresa, perfil master, permissões e usuário admin).
 * Uso: php database/seed.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

Database\Seeders\DatabaseSeeder::run();
