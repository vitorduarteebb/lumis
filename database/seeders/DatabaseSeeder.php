<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use PDO;

final class DatabaseSeeder
{
    public static function run(): void
    {
        $pdo = Database::connection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();

        $companyId = self::seedCompany($pdo);
        $storeId = self::seedStore($pdo, $companyId);
        self::seedProductLookups($pdo, $companyId);
        $roleId = self::seedMasterRole($pdo);
        self::seedPermissions($pdo);
        self::seedRolePermissionsForMaster($pdo, $roleId);
        $userId = self::seedAdminUser($pdo, $companyId, $storeId);
        self::seedUserRole($pdo, $userId, $roleId);
        self::seedUserCompany($pdo, $userId, $companyId);
        self::seedUserStore($pdo, $userId, $storeId);

        $pdo->commit();

        echo "Seed concluído: usuário admin@lumiserp.com / 123456" . PHP_EOL;
    }

    private static function seedCompany(PDO $pdo): int
    {
        $sql = <<<SQL
INSERT INTO companies (name, slug, status, created_at, updated_at)
VALUES ('Empresa Matriz', 'empresa-matriz', 1, NOW(), NOW())
SQL;
        $pdo->exec($sql);
        return (int) $pdo->lastInsertId();
    }

    private static function seedStore(PDO $pdo, int $companyId): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO stores (company_id, name, slug, status, created_at, updated_at)
             VALUES (:company_id, :name, :slug, 1, NOW(), NOW())'
        );
        $stmt->execute([
            'company_id' => $companyId,
            'name' => 'Loja Matriz',
            'slug' => 'matriz',
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Categorias, marcas e unidades padrão (requer migration 002).
     */
    private static function seedProductLookups(PDO $pdo, int $companyId): void
    {
        $check = $pdo->query("SHOW TABLES LIKE 'product_categories'")->fetchColumn();
        if (!$check) {
            return;
        }

        $insCat = $pdo->prepare(
            'INSERT IGNORE INTO product_categories (company_id, name, slug, status, created_at, updated_at)
             VALUES (:cid, :name, :slug, 1, NOW(), NOW())'
        );
        foreach ([['Geral', 'geral'], ['Revenda', 'revenda'], ['Matéria-prima', 'materia-prima']] as $c) {
            $insCat->execute(['cid' => $companyId, 'name' => $c[0], 'slug' => $c[1]]);
        }

        $insBrand = $pdo->prepare(
            'INSERT IGNORE INTO product_brands (company_id, name, status, created_at, updated_at)
             VALUES (:cid, :name, 1, NOW(), NOW())'
        );
        foreach (['Marca própria', 'Distribuidor'] as $bn) {
            $insBrand->execute(['cid' => $companyId, 'name' => $bn]);
        }

        $insUnit = $pdo->prepare(
            'INSERT IGNORE INTO product_units (company_id, name, abbreviation, status, created_at, updated_at)
             VALUES (:cid, :name, :abbr, 1, NOW(), NOW())'
        );
        foreach ([['Unidade', 'UN'], ['Quilograma', 'KG'], ['Caixa', 'CX']] as $u) {
            $insUnit->execute(['cid' => $companyId, 'name' => $u[0], 'abbr' => $u[1]]);
        }
    }

    private static function seedMasterRole(PDO $pdo): int
    {
        $existing = $pdo->query("SELECT id FROM roles WHERE slug = 'master' LIMIT 1")->fetchColumn();
        if ($existing !== false && (int) $existing > 0) {
            return (int) $existing;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO roles (name, slug, description, created_at, updated_at)
             VALUES (:name, :slug, :description, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => 'Master',
            'slug' => 'master',
            'description' => 'Acesso total ao sistema (via permissões atribuídas).',
        ]);

        return (int) $pdo->lastInsertId();
    }

    private static function seedPermissions(PDO $pdo): void
    {
        $rows = self::permissionDefinitions();
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO permissions (name, slug, module, action, created_at, updated_at)
             VALUES (:name, :slug, :module, :action, NOW(), NOW())'
        );

        foreach ($rows as $row) {
            $stmt->execute([
                'name' => $row['name'],
                'slug' => $row['slug'],
                'module' => $row['module'],
                'action' => $row['action'],
            ]);
        }
    }

    /**
     * @return list<array{name: string, slug: string, module: string, action: string}>
     */
    private static function permissionDefinitions(): array
    {
        $fromConfig = require config_path('permissions.php');
        if (!is_array($fromConfig)) {
            return [];
        }

        $crud = [
            ['name' => 'Clientes — criar (legado)', 'slug' => 'clients.create', 'module' => 'cadastros', 'action' => 'create'],
            ['name' => 'Clientes — editar (legado)', 'slug' => 'clients.edit', 'module' => 'cadastros', 'action' => 'edit'],
            ['name' => 'Fornecedores — criar (legado)', 'slug' => 'suppliers.create', 'module' => 'cadastros', 'action' => 'create'],
            ['name' => 'Fornecedores — editar (legado)', 'slug' => 'suppliers.edit', 'module' => 'cadastros', 'action' => 'edit'],
            ['name' => 'Funcionários — criar', 'slug' => 'employees.create', 'module' => 'cadastros', 'action' => 'create'],
            ['name' => 'Funcionários — editar', 'slug' => 'employees.edit', 'module' => 'cadastros', 'action' => 'edit'],
            ['name' => 'Transportadoras — criar', 'slug' => 'carriers.create', 'module' => 'cadastros', 'action' => 'create'],
            ['name' => 'Transportadoras — editar', 'slug' => 'carriers.edit', 'module' => 'cadastros', 'action' => 'edit'],
            ['name' => 'Produtos — criar (legado)', 'slug' => 'products.create', 'module' => 'produtos', 'action' => 'create'],
            ['name' => 'Produtos — editar (legado)', 'slug' => 'products.edit', 'module' => 'produtos', 'action' => 'edit'],
            ['name' => 'Serviços — criar (legado)', 'slug' => 'services.create', 'module' => 'servicos', 'action' => 'create'],
            ['name' => 'Serviços — editar (legado)', 'slug' => 'services.edit', 'module' => 'servicos', 'action' => 'edit'],
        ];

        return array_merge($fromConfig, $crud);
    }

    private static function seedRolePermissionsForMaster(PDO $pdo, int $roleId): void
    {
        $stmt = $pdo->query('SELECT id FROM permissions');
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $ins = $pdo->prepare(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)'
        );
        foreach ($ids as $pid) {
            $ins->execute([
                'role_id' => $roleId,
                'permission_id' => (int) $pid,
            ]);
        }
    }

    private static function seedAdminUser(PDO $pdo, int $companyId, int $storeId): int
    {
        $hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (company_id, store_id, name, email, password, status, created_at, updated_at)
             VALUES (:company_id, :store_id, :name, :email, :password, 1, NOW(), NOW())'
        );
        $stmt->execute([
            'company_id' => $companyId,
            'store_id' => $storeId,
            'name' => 'Administrador Master',
            'email' => 'admin@lumiserp.com',
            'password' => $hash,
        ]);
        return (int) $pdo->lastInsertId();
    }

    private static function seedUserRole(PDO $pdo, int $userId, int $roleId): void
    {
        $stmt = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)');
        $stmt->execute([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    private static function seedUserCompany(PDO $pdo, int $userId, int $companyId): void
    {
        $stmt = $pdo->prepare('INSERT INTO user_companies (user_id, company_id) VALUES (:user_id, :company_id)');
        $stmt->execute([
            'user_id' => $userId,
            'company_id' => $companyId,
        ]);
    }

    private static function seedUserStore(PDO $pdo, int $userId, int $storeId): void
    {
        $stmt = $pdo->prepare('INSERT INTO user_stores (user_id, store_id) VALUES (:user_id, :store_id)');
        $stmt->execute([
            'user_id' => $userId,
            'store_id' => $storeId,
        ]);
    }
}
