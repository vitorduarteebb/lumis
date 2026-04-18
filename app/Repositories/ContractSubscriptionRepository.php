<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ContractSubscriptionRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        int $companyId,
        string $search,
        string $status,
        ?int $clientId,
        ?string $dateFrom,
        ?string $dateTo,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['t.company_id = :cid', 't.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($status !== '' && $status !== 'all') {
            $where[] = 't.status = :st';
            $params['st'] = $status;
        }
        if ($clientId !== null && $clientId > 0) {
            $where[] = 't.client_id = :cl';
            $params['cl'] = $clientId;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 't.start_date >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'COALESCE(t.next_billing_date, t.created_at) <= :dt';
            $params['dt'] = $dateTo;
        }
        if ($search !== '') {
            $where[] = '(t.subscription_number LIKE :sq OR t.plan_description LIKE :sq OR c.name LIKE :sq)';
            $params['sq'] = '%' . $search . '%';
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM contract_subscriptions t LEFT JOIN clients c ON c.id = t.client_id WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT t.*, c.name AS client_name FROM contract_subscriptions t
                LEFT JOIN clients c ON c.id = t.client_id
                WHERE {$whereSql} ORDER BY t.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    public function findById(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT t.*, c.name AS client_name FROM contract_subscriptions t
             LEFT JOIN clients c ON c.id = t.client_id
             WHERE t.id = :id AND t.company_id = :cid AND t.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function insert(int $companyId, array $data, ?int $userId): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO contract_subscriptions (
                company_id, store_id, subscription_number, client_id, plan_description, recurring_amount, periodicity_entry_id,
                start_date, next_billing_date, status, notes, attachment_path, created_by, created_at, updated_at
            ) VALUES (
                :cid, :sid, :snum, :clid, :plan, :amt, :per, :sd, :nb, :st, :notes, :att, :cb, NOW(), NOW()
            )'
        );
        $stmt->execute([
            'cid' => $companyId,
            'sid' => $data['store_id'] ?? null,
            'snum' => $data['subscription_number'] ?? null,
            'clid' => $data['client_id'],
            'plan' => $data['plan_description'] ?? null,
            'amt' => $data['recurring_amount'] ?? 0,
            'per' => $data['periodicity_entry_id'] ?? null,
            'sd' => $data['start_date'] ?? null,
            'nb' => $data['next_billing_date'] ?? null,
            'st' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
            'att' => $data['attachment_path'] ?? null,
            'cb' => $userId,
        ]);
        $id = (int) $this->pdo()->lastInsertId();
        if (empty($data['subscription_number'])) {
            $this->pdo()->prepare('UPDATE contract_subscriptions SET subscription_number = :n WHERE id = :id')->execute([
                'n' => 'AS-' . $id,
                'id' => $id,
            ]);
        }

        return $id;
    }

    public function update(int $id, int $companyId, array $data, ?int $userId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE contract_subscriptions SET
                store_id = :sid, subscription_number = :snum, client_id = :clid, plan_description = :plan, recurring_amount = :amt,
                periodicity_entry_id = :per, start_date = :sd, next_billing_date = :nb, status = :st, notes = :notes,
                attachment_path = COALESCE(:att, attachment_path), updated_by = :ub, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute([
            'id' => $id,
            'cid' => $companyId,
            'sid' => $data['store_id'] ?? null,
            'snum' => $data['subscription_number'] ?? null,
            'clid' => $data['client_id'],
            'plan' => $data['plan_description'] ?? null,
            'amt' => $data['recurring_amount'] ?? 0,
            'per' => $data['periodicity_entry_id'] ?? null,
            'sd' => $data['start_date'] ?? null,
            'nb' => $data['next_billing_date'] ?? null,
            'st' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
            'att' => $data['attachment_path'] ?? null,
            'ub' => $userId,
        ]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Assinatura não encontrada.');
        }
    }

    public function setStatus(int $id, int $companyId, string $status, ?int $userId): void
    {
        $this->pdo()->prepare(
            'UPDATE contract_subscriptions SET status = :st, updated_by = :ub, updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        )->execute(['st' => $status, 'ub' => $userId, 'id' => $id, 'cid' => $companyId]);
    }

    public function setAttachmentPath(int $id, int $companyId, string $path, ?int $userId): void
    {
        $this->pdo()->prepare(
            'UPDATE contract_subscriptions SET attachment_path = :p, updated_by = :ub, updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        )->execute(['p' => $path, 'ub' => $userId, 'id' => $id, 'cid' => $companyId]);
    }
}
