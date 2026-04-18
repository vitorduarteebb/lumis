<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AccountPayableRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(int $companyId, string $status, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = 'ap.company_id = :cid AND ap.deleted_at IS NULL';
        $params = ['cid' => $companyId];
        if ($status !== '' && in_array($status, ['open', 'paid', 'cancelled'], true)) {
            $where .= ' AND ap.status = :st';
            $params['st'] = $status;
        }
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM accounts_payable ap WHERE {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT ap.*, s.name AS supplier_name FROM accounts_payable ap
                LEFT JOIN suppliers s ON s.id = ap.supplier_id
                WHERE {$where} ORDER BY ap.due_date ASC, ap.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM accounts_payable WHERE id = :id AND company_id = :cid AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO accounts_payable (company_id, supplier_id, description, amount, due_date, status, paid_amount, created_at, updated_at)
             VALUES (:cid, :sid, :desc, :amt, :due, :st, 0, NOW(), NOW())'
        );
        $sid = $data['supplier_id'] ?? null;
        if ($sid === '' || $sid === null || $sid === '0') {
            $sid = null;
        } else {
            $sid = (int) $sid;
        }
        $stmt->execute([
            'cid' => $companyId,
            'sid' => $sid,
            'desc' => $data['description'],
            'amt' => $data['amount'],
            'due' => $data['due_date'],
            'st' => $data['status'] ?? 'open',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE accounts_payable SET supplier_id = :sid, description = :desc, amount = :amt, due_date = :due, status = :st, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $sid = $data['supplier_id'] ?? null;
        if ($sid === '' || $sid === null || $sid === '0') {
            $sid = null;
        } else {
            $sid = (int) $sid;
        }
        $stmt->execute([
            'sid' => $sid,
            'desc' => $data['description'],
            'amt' => $data['amount'],
            'due' => $data['due_date'],
            'st' => $data['status'],
            'id' => $id,
            'cid' => $companyId,
        ]);
    }

    public function addPayment(int $id, int $companyId, string $amount): void
    {
        $row = $this->findById($id, $companyId);
        if ($row === null) {
            return;
        }
        $paid = (float) $row['paid_amount'] + (float) $amount;
        $total = (float) $row['amount'];
        $status = $paid >= $total - 0.0001 ? 'paid' : 'open';
        $stmt = $this->pdo()->prepare(
            'UPDATE accounts_payable SET paid_amount = :paid, status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['paid' => $paid, 'st' => $status, 'id' => $id, 'cid' => $companyId]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE accounts_payable SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }

    /**
     * @return array{receitas: float, despesas: float}
     */
    public function totalsForPeriod(int $companyId, string $start, string $end): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COALESCE(SUM(amount),0) FROM accounts_payable WHERE company_id = :cid AND deleted_at IS NULL
             AND status != \'cancelled\' AND due_date BETWEEN :s AND :e'
        );
        $stmt->execute(['cid' => $companyId, 's' => $start, 'e' => $end]);
        $desp = (float) $stmt->fetchColumn();

        return ['receitas' => 0.0, 'despesas' => $desp];
    }
}
