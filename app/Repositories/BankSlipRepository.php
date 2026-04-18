<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class BankSlipRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listByCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM bank_slip_records WHERE company_id = :cid ORDER BY due_date ASC, id DESC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(int $companyId, array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO bank_slip_records (company_id, payer_name, amount, due_date, status, our_number, notes, created_at)
             VALUES (:cid, :payer, :amt, :due, :st, :our, :notes, NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'payer' => $data['payer_name'],
            'amt' => $data['amount'],
            'due' => $data['due_date'],
            'st' => $data['status'] ?? 'pending',
            'our' => $data['our_number'],
            'notes' => $data['notes'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateStatus(int $id, int $companyId, string $status): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE bank_slip_records SET status = :st WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['st' => $status, 'id' => $id, 'cid' => $companyId]);
    }
}
