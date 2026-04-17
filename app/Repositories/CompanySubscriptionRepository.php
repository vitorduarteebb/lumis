<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CompanySubscriptionRepository extends BaseRepository
{
    public function ensureRow(int $companyId): void
    {
        $stmt = $this->pdo()->prepare('SELECT id FROM company_subscriptions WHERE company_id = :cid LIMIT 1');
        $stmt->execute(['cid' => $companyId]);
        if ($stmt->fetchColumn()) {
            return;
        }
        $this->pdo()->prepare(
            'INSERT INTO company_subscriptions (company_id, plan_name, status, max_users, created_at, updated_at)
             VALUES (:cid, :plan, :st, 50, NOW(), NOW())'
        )->execute(['cid' => $companyId, 'plan' => 'Standard', 'st' => 'active']);
    }

    /**
     * @return array<string, mixed>
     */
    public function findByCompanyId(int $companyId): array
    {
        $this->ensureRow($companyId);
        $stmt = $this->pdo()->prepare('SELECT * FROM company_subscriptions WHERE company_id = :cid LIMIT 1');
        $stmt->execute(['cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSubscription(int $companyId, array $data): void
    {
        $this->ensureRow($companyId);
        $allowed = ['plan_name', 'status', 'max_users', 'renews_at', 'notes'];
        $set = [];
        $params = ['cid' => $companyId];
        foreach ($allowed as $f) {
            if (!array_key_exists($f, $data)) {
                continue;
            }
            $set[] = "{$f} = :{$f}";
            $params[$f] = $data[$f];
        }
        if ($set === []) {
            return;
        }
        $sql = 'UPDATE company_subscriptions SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE company_id = :cid';
        $this->pdo()->prepare($sql)->execute($params);
    }
}
