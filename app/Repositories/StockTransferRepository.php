<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class StockTransferRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(int $companyId, string $status, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['t.company_id = :cid'];
        $params = ['cid' => $companyId];
        if ($status !== '' && $status !== 'all') {
            $where[] = 't.status = :st';
            $params['st'] = $status;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM stock_transfers t WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT t.*, sf.name AS from_store_name, st.name AS to_store_name, u.name AS user_name
                FROM stock_transfers t
                LEFT JOIN stores sf ON sf.id = t.from_store_id
                LEFT JOIN stores st ON st.id = t.to_store_id
                LEFT JOIN users u ON u.id = t.created_by
                WHERE {$whereSql}
                ORDER BY t.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @param list<array{product_id: int, qty: float}> $items
     */
    public function create(int $companyId, int $fromStoreId, int $toStoreId, ?string $notes, ?int $userId, array $items): int
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO stock_transfers (company_id, from_store_id, to_store_id, status, notes, created_by, created_at)
             VALUES (:cid, :f, :t, \'pending\', :n, :u, NOW())'
        );
        $stmt->execute(['cid' => $companyId, 'f' => $fromStoreId, 't' => $toStoreId, 'n' => $notes, 'u' => $userId]);
        $tid = (int) $pdo->lastInsertId();
        $ins = $pdo->prepare(
            'INSERT INTO stock_transfer_items (transfer_id, product_id, qty) VALUES (:tid, :pid, :qty)'
        );
        foreach ($items as $it) {
            $ins->execute(['tid' => $tid, 'pid' => $it['product_id'], 'qty' => $it['qty']]);
        }

        return $tid;
    }

    /**
     * @return array{transfer: array<string, mixed>, items: list<array<string, mixed>>}|null
     */
    public function findWithItems(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT t.*, sf.name AS from_store_name, st.name AS to_store_name
             FROM stock_transfers t
             LEFT JOIN stores sf ON sf.id = t.from_store_id
             LEFT JOIN stores st ON st.id = t.to_store_id
             WHERE t.id = :id AND t.company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $transfer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($transfer === false) {
            return null;
        }
        $stmt = $this->pdo()->prepare(
            'SELECT i.*, p.name AS product_name, p.sku FROM stock_transfer_items i
             LEFT JOIN products p ON p.id = i.product_id
             WHERE i.transfer_id = :tid ORDER BY i.id'
        );
        $stmt->execute(['tid' => $id]);

        return ['transfer' => $transfer, 'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function setStatus(int $id, int $companyId, string $status): void
    {
        $this->pdo()->prepare(
            'UPDATE stock_transfers SET status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        )->execute(['st' => $status, 'id' => $id, 'cid' => $companyId]);
    }
}
