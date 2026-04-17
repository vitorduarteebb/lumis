<?php

declare(strict_types=1);

/**
 * Executa arquivos SQL em database/migrations/ em ordem lexicográfica.
 * Uso: php database/migrate.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

use App\Core\Database;

$migrationsDir = dirname(__DIR__) . '/database/migrations';
$files = glob($migrationsDir . '/*.sql') ?: [];
sort($files);

if ($files === []) {
    fwrite(STDERR, "Nenhum arquivo .sql encontrado em database/migrations/\n");
    exit(1);
}

$pdo = Database::connection();
$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 0);

foreach ($files as $file) {
    echo "Executando: " . basename($file) . PHP_EOL;
    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "Falha ao ler: {$file}\n");
        exit(1);
    }

    foreach (splitSqlStatements($sql) as $statement) {
        if ($statement === '') {
            continue;
        }
        $pdo->exec($statement);
    }
}

echo "Migrations concluídas." . PHP_EOL;

/**
 * @return list<string>
 */
function splitSqlStatements(string $sql): array
{
    $lines = preg_split('/\R/', $sql) ?: [];
    $out = [];
    $buffer = '';
    foreach ($lines as $line) {
        $trim = ltrim($line);
        if ($trim === '' || str_starts_with($trim, '--')) {
            continue;
        }
        $buffer .= $line . "\n";
        if (preg_match('/;\s*$/', $line)) {
            $stmt = trim($buffer);
            if ($stmt !== '') {
                $out[] = rtrim($stmt, " \t\n\r\0\x0B;");
            }
            $buffer = '';
        }
    }
    $tail = trim($buffer);
    if ($tail !== '') {
        $out[] = rtrim($tail, ';');
    }
    return $out;
}
