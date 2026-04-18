<?php

declare(strict_types=1);

/**
 * Garante o usuário admin@lumiserp.com com senha conhecida (padrão: 123456).
 * Cria empresa/loja se não existirem; vincula ao papel master e à empresa.
 *
 * Uso na VPS:
 *   cd /var/www/lumis
 *   php database/bootstrap_admin.php
 *   php database/bootstrap_admin.php "OutraSenha123"
 */

require dirname(__DIR__) . '/vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$plain = $argv[1] ?? '123456';
if (strlen($plain) < 6) {
    fwrite(STDERR, "Senha deve ter no mínimo 6 caracteres.\n");
    exit(1);
}

use App\Core\Database;

$pdo = Database::connection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

const ADMIN_EMAIL = 'admin@lumiserp.com';

$hash = password_hash($plain, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'SELECT id, status, deleted_at FROM users WHERE LOWER(TRIM(email)) = LOWER(:e) LIMIT 1'
);
$stmt->execute(['e' => ADMIN_EMAIL]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row !== false) {
    $uid = (int) $row['id'];
    if ($row['deleted_at'] !== null) {
        $pdo->prepare('UPDATE users SET deleted_at = NULL, status = 1, password = :p, updated_at = NOW() WHERE id = :id')
            ->execute(['p' => $hash, 'id' => $uid]);
        echo "Usuário reativado e senha atualizada (ID {$uid}).\n";
    } else {
        $pdo->prepare('UPDATE users SET password = :p, status = 1, updated_at = NOW() WHERE id = :id')
            ->execute(['p' => $hash, 'id' => $uid]);
        echo "Senha redefinida para o usuário existente (ID {$uid}).\n";
    }

    $st = $pdo->prepare('SELECT company_id, store_id FROM users WHERE id = :id');
    $st->execute(['id' => $uid]);
    $urow = $st->fetch(PDO::FETCH_ASSOC);
    $cid = (int) ($urow['company_id'] ?? 0);
    $sid = (int) ($urow['store_id'] ?? 0);
    $roleRow = $pdo->query("SELECT id FROM roles WHERE slug = 'master' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $roleId = $roleRow !== false ? (int) $roleRow['id'] : 0;
    if ($roleId > 0) {
        $pdo->prepare('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:u, :r)')
            ->execute(['u' => $uid, 'r' => $roleId]);
    }
    if ($cid > 0) {
        $pdo->prepare('INSERT IGNORE INTO user_companies (user_id, company_id) VALUES (:u, :c)')
            ->execute(['u' => $uid, 'c' => $cid]);
    }
    if ($sid > 0) {
        $pdo->prepare('INSERT IGNORE INTO user_stores (user_id, store_id) VALUES (:u, :s)')
            ->execute(['u' => $uid, 's' => $sid]);
    }
} else {
    $cid = (int) $pdo->query('SELECT id FROM companies ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($cid < 1) {
        $pdo->exec(
            "INSERT INTO companies (name, slug, status, created_at, updated_at)
             VALUES ('Empresa Matriz', 'empresa-matriz', 1, NOW(), NOW())"
        );
        $cid = (int) $pdo->lastInsertId();
        echo "Empresa criada (ID {$cid}).\n";
    }

    $sid = (int) $pdo->query("SELECT id FROM stores WHERE company_id = " . (int) $cid . " ORDER BY id ASC LIMIT 1")->fetchColumn();
    if ($sid < 1) {
        $st = $pdo->prepare(
            'INSERT INTO stores (company_id, name, slug, status, created_at, updated_at)
             VALUES (:cid, :name, :slug, 1, NOW(), NOW())'
        );
        $st->execute(['cid' => $cid, 'name' => 'Loja Matriz', 'slug' => 'matriz']);
        $sid = (int) $pdo->lastInsertId();
        echo "Loja criada (ID {$sid}).\n";
    }

    $stRole = $pdo->prepare('SELECT id FROM roles WHERE slug = :s LIMIT 1');
    $stRole->execute(['s' => 'master']);
    $roleId = (int) $stRole->fetchColumn();
    if ($roleId < 1) {
        $pdo->prepare(
            'INSERT INTO roles (name, slug, description, created_at, updated_at)
             VALUES (:n, :slug, :d, NOW(), NOW())'
        )->execute([
            'n' => 'Master',
            'slug' => 'master',
            'd' => 'Acesso total (bootstrap).',
        ]);
        $roleId = (int) $pdo->lastInsertId();
        echo "Papel master criado (ID {$roleId}).\n";
    }

    $pdo->prepare(
        'INSERT INTO users (company_id, store_id, name, email, password, status, created_at, updated_at)
         VALUES (:cid, :sid, :name, :email, :password, 1, NOW(), NOW())'
    )->execute([
        'cid' => $cid,
        'sid' => $sid,
        'name' => 'Administrador Master',
        'email' => ADMIN_EMAIL,
        'password' => $hash,
    ]);
    $uid = (int) $pdo->lastInsertId();
    echo "Usuário admin criado (ID {$uid}).\n";

    $pdo->prepare('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:u, :r)')
        ->execute(['u' => $uid, 'r' => $roleId]);
    $pdo->prepare('INSERT IGNORE INTO user_companies (user_id, company_id) VALUES (:u, :c)')
        ->execute(['u' => $uid, 'c' => $cid]);
    $pdo->prepare('INSERT IGNORE INTO user_stores (user_id, store_id) VALUES (:u, :s)')
        ->execute(['u' => $uid, 's' => $sid]);
}

echo "\nLogin: " . ADMIN_EMAIL . "\nSenha: (a que você passou ou 123456 por padrão)\n";
echo "Se o master não tiver permissões, rode: php database/sync_permissions.php\n";
