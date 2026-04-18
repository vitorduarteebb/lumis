<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class LookupRepository extends BaseRepository
{
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM lookup_entries WHERE id = :id AND company_id = :cid AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginateByType(int $companyId, string $entryType, string $search, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['company_id = :cid', 'entry_type = :et', 'deleted_at IS NULL'];
        $params = ['cid' => $companyId, 'et' => $entryType];
        if ($search !== '') {
            $where[] = 'name LIKE :q';
            $params['q'] = '%' . $search . '%';
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM lookup_entries WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $sql = "SELECT * FROM lookup_entries WHERE {$whereSql} ORDER BY sort_order ASC, name ASC LIMIT "
            . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    public function slugExists(int $companyId, string $entryType, string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM lookup_entries WHERE company_id = :cid AND entry_type = :et AND slug = :sl AND deleted_at IS NULL';
        $params = ['cid' => $companyId, 'et' => $entryType, 'sl' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id != :eid';
            $params['eid'] = $exceptId;
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data): int
    {
        if ($this->columnExists('lookup_entries', 'value_text')) {
            $stmt = $this->pdo()->prepare(
                'INSERT INTO lookup_entries (company_id, entry_type, name, slug, value_text, sort_order, status, created_at, updated_at)
                 VALUES (:company_id, :entry_type, :name, :slug, :value_text, :sort_order, :status, NOW(), NOW())'
            );
            $stmt->execute([
                'company_id' => $companyId,
                'entry_type' => $data['entry_type'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'value_text' => $data['value_text'] ?? null,
                'sort_order' => $data['sort_order'],
                'status' => $data['status'],
            ]);
        } else {
            $stmt = $this->pdo()->prepare(
                'INSERT INTO lookup_entries (company_id, entry_type, name, slug, sort_order, status, created_at, updated_at)
                 VALUES (:company_id, :entry_type, :name, :slug, :sort_order, :status, NOW(), NOW())'
            );
            $stmt->execute([
                'company_id' => $companyId,
                'entry_type' => $data['entry_type'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'sort_order' => $data['sort_order'],
                'status' => $data['status'],
            ]);
        }

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data): void
    {
        $hasVtKey = array_key_exists('value_text', $data);
        $hasVtCol = $this->columnExists('lookup_entries', 'value_text');
        if ($hasVtKey && $hasVtCol) {
            $sql = 'UPDATE lookup_entries SET name = :name, slug = :slug, value_text = :value_text, sort_order = :sort_order, status = :status, updated_at = NOW()
                    WHERE id = :id AND company_id = :cid AND deleted_at IS NULL';
            $params = [
                'id' => $id,
                'cid' => $companyId,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'value_text' => $data['value_text'],
                'sort_order' => $data['sort_order'],
                'status' => $data['status'],
            ];
        } else {
            $sql = 'UPDATE lookup_entries SET name = :name, slug = :slug, sort_order = :sort_order, status = :status, updated_at = NOW()
                    WHERE id = :id AND company_id = :cid AND deleted_at IS NULL';
            $params = [
                'id' => $id,
                'cid' => $companyId,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'sort_order' => $data['sort_order'],
                'status' => $data['status'],
            ];
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE lookup_entries SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }
}
