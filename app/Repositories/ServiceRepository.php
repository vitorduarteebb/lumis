<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ServiceRepository extends BaseRepository
{
    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $sql = 'SELECT * FROM services WHERE id = :id AND company_id = :cid AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(int $companyId, string $search, ?string $statusFilter, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = ['company_id = :cid', 'deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($search !== '') {
            $where[] = '(name LIKE :q OR category LIKE :q2 OR description LIKE :q3)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
        }
        if ($statusFilter === '1' || $statusFilter === '0') {
            $where[] = 'status = :st';
            $params['st'] = (int) $statusFilter;
        }
        $whereSql = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) FROM services WHERE {$whereSql}";
        $stmt = $this->pdo()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT * FROM services WHERE {$whereSql} ORDER BY name ASC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data, ?int $createdBy): int
    {
        $sql = 'INSERT INTO services (
            company_id, name, category, price, duration_minutes, description, status, created_by, created_at, updated_at
        ) VALUES (
            :company_id, :name, :category, :price, :duration_minutes, :description, :status, :created_by, NOW(), NOW()
        )';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'],
            'description' => $data['description'],
            'status' => $data['status'],
            'created_by' => $createdBy,
        ]);
        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data, ?int $updatedBy): void
    {
        $sql = 'UPDATE services SET
            name = :name, category = :category, price = :price, duration_minutes = :duration_minutes,
            description = :description, status = :status, updated_by = :updated_by, updated_at = NOW()
            WHERE id = :id AND company_id = :cid AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'cid' => $companyId,
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'],
            'description' => $data['description'],
            'status' => $data['status'],
            'updated_by' => $updatedBy,
        ]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE services SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForSelect(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name, price FROM services WHERE company_id = :cid AND deleted_at IS NULL AND status = 1 ORDER BY name ASC LIMIT 500'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
