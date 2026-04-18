<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository extends BaseRepository
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT * FROM users WHERE LOWER(TRIM(email)) = LOWER(TRIM(:email)) AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, ?int $companyId): ?array
    {
        if ($companyId === null) {
            return $this->findById($id);
        }
        $sql = 'SELECT * FROM users WHERE id = :id AND company_id = :cid AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function updateLastLogin(int $userId, ?\DateTimeInterface $at = null): void
    {
        $at ??= new \DateTimeImmutable('now');
        $sql = 'UPDATE users SET last_login_at = :ts, updated_at = NOW() WHERE id = :id';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'ts' => $at->format('Y-m-d H:i:s'),
            'id' => $userId,
        ]);
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $sql = 'UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'password' => $passwordHash,
            'id' => $userId,
        ]);
    }

    public function emailExists(string $email, ?int $exceptUserId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE LOWER(TRIM(email)) = LOWER(TRIM(:email)) AND deleted_at IS NULL';
        $params = ['email' => $email];
        if ($exceptUserId !== null) {
            $sql .= ' AND id != :eid';
            $params['eid'] = $exceptUserId;
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @return list<int>
     */
    public function roleIdsForUser(int $userId): array
    {
        $stmt = $this->pdo()->prepare('SELECT role_id FROM user_roles WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_map(static fn ($v) => (int) $v, $rows);
    }

    /**
     * @param list<int> $roleIds
     */
    public function syncRoles(int $userId, array $roleIds): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM user_roles WHERE user_id = :uid')->execute(['uid' => $userId]);
        if ($roleIds === []) {
            return;
        }
        $ins = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:uid, :rid)');
        foreach ($roleIds as $rid) {
            $rid = (int) $rid;
            if ($rid > 0) {
                $ins->execute(['uid' => $userId, 'rid' => $rid]);
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function paginate(?int $companyId, string $search, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = ['u.deleted_at IS NULL'];
        $params = [];
        if ($companyId !== null) {
            $where[] = 'u.company_id = :cid';
            $params['cid'] = $companyId;
        }
        if ($search !== '') {
            $where[] = '(u.name LIKE :q OR u.email LIKE :q2)';
            $params['q'] = '%' . $search . '%';
            $params['q2'] = '%' . $search . '%';
        }
        $whereSql = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) FROM users u WHERE {$whereSql}";
        $stmt = $this->pdo()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT u.*, c.name AS company_name, s.name AS store_name
                FROM users u
                LEFT JOIN companies c ON c.id = u.company_id
                LEFT JOIN stores s ON s.id = u.store_id
                WHERE {$whereSql}
                ORDER BY u.name ASC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * @param array{name: string, email: string, password_hash: string, company_id: int|null, store_id: int|null, status: int} $data
     */
    public function insert(array $data): int
    {
        $driver = isset($data['is_delivery_driver']) ? ((int) $data['is_delivery_driver'] === 1 ? 1 : 0) : 0;
        if ($this->columnExists('users', 'is_delivery_driver')) {
            $sql = 'INSERT INTO users (company_id, store_id, name, email, password, status, is_delivery_driver, created_at, updated_at)
                    VALUES (:company_id, :store_id, :name, :email, :password, :status, :is_delivery_driver, NOW(), NOW())';
            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute([
                'company_id' => $data['company_id'],
                'store_id' => $data['store_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password_hash'],
                'status' => $data['status'],
                'is_delivery_driver' => $driver,
            ]);
        } else {
            $sql = 'INSERT INTO users (company_id, store_id, name, email, password, status, created_at, updated_at)
                    VALUES (:company_id, :store_id, :name, :email, :password, :status, NOW(), NOW())';
            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute([
                'company_id' => $data['company_id'],
                'store_id' => $data['store_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password_hash'],
                'status' => $data['status'],
            ]);
        }

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array{name?: string, email?: string, password_hash?: string|null, company_id?: int|null, store_id?: int|null, status?: int} $data
     */
    public function updateUser(int $id, array $data): void
    {
        $sets = ['updated_at = NOW()'];
        $params = ['id' => $id];
        if (array_key_exists('name', $data)) {
            $sets[] = 'name = :name';
            $params['name'] = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $sets[] = 'email = :email';
            $params['email'] = $data['email'];
        }
        if (array_key_exists('password_hash', $data) && $data['password_hash'] !== null && $data['password_hash'] !== '') {
            $sets[] = 'password = :password';
            $params['password'] = $data['password_hash'];
        }
        if (array_key_exists('company_id', $data)) {
            $sets[] = 'company_id = :company_id';
            $params['company_id'] = $data['company_id'];
        }
        if (array_key_exists('store_id', $data)) {
            $sets[] = 'store_id = :store_id';
            $params['store_id'] = $data['store_id'];
        }
        if (array_key_exists('status', $data)) {
            $sets[] = 'status = :status';
            $params['status'] = $data['status'];
        }
        if (array_key_exists('is_delivery_driver', $data) && $this->columnExists('users', 'is_delivery_driver')) {
            $sets[] = 'is_delivery_driver = :is_delivery_driver';
            $params['is_delivery_driver'] = (int) $data['is_delivery_driver'] === 1 ? 1 : 0;
        }
        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->pdo()->prepare('UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Nomes de papéis para exibição (listagem).
     *
     * @return list<string>
     */
    public function roleLabelsForUser(int $userId): array
    {
        $sql = 'SELECT r.name FROM roles r
                INNER JOIN user_roles ur ON ur.role_id = r.id
                WHERE ur.user_id = :uid ORDER BY r.name';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Usuários ativos da empresa (técnicos / responsáveis em O.S.).
     *
     * @return list<array<string, mixed>>
     */
    public function listActiveForCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name, email FROM users WHERE company_id = :cid AND deleted_at IS NULL AND status = 1 ORDER BY name ASC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Entregadores marcados para locações (logística).
     *
     * @return list<array<string, mixed>>
     */
    public function listDeliveryDriversForCompany(int $companyId): array
    {
        if ($this->columnExists('users', 'is_delivery_driver')) {
            $stmt = $this->pdo()->prepare(
                'SELECT id, name, email FROM users WHERE company_id = :cid AND deleted_at IS NULL AND status = 1 AND is_delivery_driver = 1 ORDER BY name ASC'
            );
        } else {
            $stmt = $this->pdo()->prepare(
                'SELECT id, name, email FROM users WHERE company_id = :cid AND deleted_at IS NULL AND status = 1 ORDER BY name ASC'
            );
        }
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
