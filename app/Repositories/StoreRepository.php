<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class StoreRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function byCompanyId(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name, slug FROM stores WHERE company_id = :cid AND status = 1 ORDER BY name ASC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM stores WHERE company_id = :cid ORDER BY name ASC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM stores WHERE id = :id AND company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function slugExists(int $companyId, string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM stores WHERE company_id = :cid AND slug = :slug';
        $params = ['cid' => $companyId, 'slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id != :eid';
            $params['eid'] = $exceptId;
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function insert(int $companyId, string $name, string $slug, int $status): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO stores (company_id, name, slug, status, created_at, updated_at) VALUES (:cid, :name, :slug, :st, NOW(), NOW())'
        );
        $stmt->execute(['cid' => $companyId, 'name' => $name, 'slug' => $slug, 'st' => $status]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, int $companyId, string $name, string $slug, int $status): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE stores SET name = :name, slug = :slug, status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['name' => $name, 'slug' => $slug, 'st' => $status, 'id' => $id, 'cid' => $companyId]);
    }

    public function setStatus(int $id, int $companyId, int $status): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE stores SET status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['st' => $status, 'id' => $id, 'cid' => $companyId]);
    }
}
