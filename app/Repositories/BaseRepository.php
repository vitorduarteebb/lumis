<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Repositório base com PDO — estender por tabela/aggregate.
 */
abstract class BaseRepository
{
    protected function pdo(): PDO
    {
        return Database::connection();
    }
}
