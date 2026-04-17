<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class EmployeeRepository extends BaseRepository
{
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM employees WHERE id = :id AND company_id = :cid AND deleted_at IS NULL LIMIT 1'
        );
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
            $where[] = '(name LIKE :q OR document LIKE :q2 OR email LIKE :q3 OR job_title LIKE :q4)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
            $params['q4'] = $w;
        }
        if ($statusFilter === '1' || $statusFilter === '0') {
            $where[] = 'status = :st';
            $params['st'] = (int) $statusFilter;
        }
        $whereSql = implode(' AND ', $where);
        $countSql = "SELECT COUNT(*) FROM employees WHERE {$whereSql}";
        $stmt = $this->pdo()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $sql = "SELECT * FROM employees WHERE {$whereSql} ORDER BY name ASC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data, ?int $createdBy): int
    {
        $sql = 'INSERT INTO employees (
            company_id, name, document, job_title, email, phone, hire_date, status, notes,
            created_by, created_at, updated_at
        ) VALUES (
            :company_id, :name, :document, :job_title, :email, :phone, :hire_date, :status, :notes,
            :created_by, NOW(), NOW()
        )';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'name' => $data['name'],
            'document' => $data['document'],
            'job_title' => $data['job_title'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'hire_date' => $data['hire_date'],
            'status' => $data['status'],
            'notes' => $data['notes'],
            'created_by' => $createdBy,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data, ?int $updatedBy): void
    {
        $sql = 'UPDATE employees SET
            name = :name, document = :document, job_title = :job_title, email = :email, phone = :phone,
            hire_date = :hire_date, status = :status, notes = :notes, updated_by = :updated_by, updated_at = NOW()
            WHERE id = :id AND company_id = :cid AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'cid' => $companyId,
            'name' => $data['name'],
            'document' => $data['document'],
            'job_title' => $data['job_title'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'hire_date' => $data['hire_date'],
            'status' => $data['status'],
            'notes' => $data['notes'],
            'updated_by' => $updatedBy,
        ]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE employees SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }
}
