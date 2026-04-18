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
    /** @var array<string, bool> */
    private static array $columnCache = [];

    /** @var array<string, bool> */
    private static array $tableExistenceCache = [];

    protected function pdo(): PDO
    {
        return Database::connection();
    }

    /**
     * Verifica coluna no schema atual (cache por pedido à BD).
     */
    protected function columnExists(string $table, string $column): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return false;
        }
        $key = $table . '.' . $column;
        if (array_key_exists($key, self::$columnCache)) {
            return self::$columnCache[$key];
        }
        $pdo = $this->pdo();
        $sql = 'SELECT COUNT(*) FROM information_schema.columns
                WHERE table_schema = DATABASE() AND table_name = ' . $pdo->quote($table) . '
                AND column_name = ' . $pdo->quote($column);
        $stmt = $pdo->query($sql);
        self::$columnCache[$key] = $stmt !== false && (int) $stmt->fetchColumn() > 0;

        return self::$columnCache[$key];
    }

    /**
     * Verifica se a tabela existe no schema atual (cache por pedido).
     * Usa information_schema com quote() — nunca placeholder em nomes de tabela.
     */
    protected function tableExists(string $table): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return false;
        }
        if (array_key_exists($table, self::$tableExistenceCache)) {
            return self::$tableExistenceCache[$table];
        }
        $pdo = $this->pdo();
        $sql = 'SELECT COUNT(*) FROM information_schema.tables
                WHERE table_schema = DATABASE() AND table_name = ' . $pdo->quote($table);
        $stmt = $pdo->query($sql);
        self::$tableExistenceCache[$table] = $stmt !== false && (int) $stmt->fetchColumn() > 0;

        return self::$tableExistenceCache[$table];
    }
}
