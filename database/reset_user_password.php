<?php

declare(strict_types=1);

/**
 * Redefine senha de um usuário no banco (uso em VPS / recuperação).
 *
 * Uso:
 *   cd /var/www/lumis
 *   php database/reset_user_password.php admin@lumiserp.com "NovaSenhaSegura123"
 *
 * Requer .env com DB_* válidos.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$email = $argv[1] ?? '';
$plain = $argv[2] ?? '';

if ($email === '' || $plain === '' || strlen($plain) < 6) {
    fwrite(STDERR, "Uso: php database/reset_user_password.php email@dominio.com \"SenhaComNoMin6Chars\"\n");
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "E-mail inválido.\n");
    exit(1);
}

use App\Core\Database;
use App\Repositories\UserRepository;

$pdo = Database::connection();
$repo = new UserRepository();
$user = $repo->findByEmail($email);

if ($user === null) {
    fwrite(STDERR, "Usuário não encontrado para este e-mail (ou conta excluída).\n");
    fwrite(STDERR, "Dica: seed padrão usa admin@lumiserp.com / 123456\n");
    exit(1);
}

$hash = password_hash($plain, PASSWORD_DEFAULT);
$repo->updatePasswordHash((int) $user['id'], $hash);

echo "Senha atualizada para o usuário ID {$user['id']} ({$email}).\n";
