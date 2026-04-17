<?php

declare(strict_types=1);

/**
 * Insere permissões novas (por slug) e associa ao perfil master.
 * Use em instalações já existentes após atualizar config/permissions.php.
 *
 * Uso: php database/sync_permissions.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$pdo = \App\Core\Database::connection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rows = require dirname(__DIR__) . '/config/permissions.php';
if (!is_array($rows)) {
    fwrite(STDERR, "config/permissions.php inválido.\n");
    exit(1);
}

$crud = [
    ['name' => 'Clientes — criar', 'slug' => 'clients.create', 'module' => 'cadastros', 'action' => 'create'],
    ['name' => 'Clientes — editar', 'slug' => 'clients.edit', 'module' => 'cadastros', 'action' => 'edit'],
    ['name' => 'Fornecedores — criar', 'slug' => 'suppliers.create', 'module' => 'cadastros', 'action' => 'create'],
    ['name' => 'Fornecedores — editar', 'slug' => 'suppliers.edit', 'module' => 'cadastros', 'action' => 'edit'],
    ['name' => 'Funcionários — criar', 'slug' => 'employees.create', 'module' => 'cadastros', 'action' => 'create'],
    ['name' => 'Funcionários — editar', 'slug' => 'employees.edit', 'module' => 'cadastros', 'action' => 'edit'],
    ['name' => 'Transportadoras — criar', 'slug' => 'carriers.create', 'module' => 'cadastros', 'action' => 'create'],
    ['name' => 'Transportadoras — editar', 'slug' => 'carriers.edit', 'module' => 'cadastros', 'action' => 'edit'],
    ['name' => 'Produtos — criar', 'slug' => 'products.create', 'module' => 'produtos', 'action' => 'create'],
    ['name' => 'Produtos — editar', 'slug' => 'products.edit', 'module' => 'produtos', 'action' => 'edit'],
    ['name' => 'Serviços — criar', 'slug' => 'services.create', 'module' => 'servicos', 'action' => 'create'],
    ['name' => 'Serviços — editar', 'slug' => 'services.edit', 'module' => 'servicos', 'action' => 'edit'],
];

$rows = array_merge($rows, $crud);

$ins = $pdo->prepare(
    'INSERT IGNORE INTO permissions (name, slug, module, action, created_at, updated_at)
     VALUES (:name, :slug, :module, :action, NOW(), NOW())'
);

$inserted = 0;
foreach ($rows as $row) {
    $ins->execute([
        'name' => $row['name'],
        'slug' => $row['slug'],
        'module' => $row['module'],
        'action' => $row['action'],
    ]);
    $inserted += (int) $ins->rowCount();
}

$roleId = (int) $pdo->query("SELECT id FROM roles WHERE slug = 'master' LIMIT 1")->fetchColumn();
if ($roleId < 1) {
    $insRole = $pdo->prepare(
        'INSERT INTO roles (name, slug, description, created_at, updated_at)
         VALUES (:name, :slug, :description, NOW(), NOW())'
    );
    try {
        $insRole->execute([
            'name' => 'Master',
            'slug' => 'master',
            'description' => 'Acesso total (criado por sync_permissions.php).',
        ]);
        $roleId = (int) $pdo->lastInsertId();
    } catch (Throwable) {
        $roleId = (int) $pdo->query("SELECT id FROM roles WHERE slug = 'master' LIMIT 1")->fetchColumn();
    }
}
if ($roleId < 1) {
    $fallback = (int) $pdo->query('SELECT id FROM roles ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($fallback > 0) {
        fwrite(STDERR, "Aviso: perfil com slug 'master' inexistente; usando role id {$fallback}. Crie um perfil com slug 'master' para o esperado pelo sistema.\n");
        $roleId = $fallback;
    }
}
if ($roleId < 1) {
    fwrite(STDERR, "Nenhum perfil (role) encontrado na tabela roles. Rode o seed inicial ou crie um perfil manualmente.\n");
    exit(1);
}

$permIds = $pdo->query('SELECT id FROM permissions')->fetchAll(PDO::FETCH_COLUMN);
$rp = $pdo->prepare('INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:rid, :pid)');
$linked = 0;
foreach ($permIds as $pid) {
    $rp->execute(['rid' => $roleId, 'pid' => (int) $pid]);
    $linked += $rp->rowCount();
}

echo "Permissões inseridas (novas): {$inserted}. Vínculos role_permissions criados agora: {$linked}. Concluído.\n";
