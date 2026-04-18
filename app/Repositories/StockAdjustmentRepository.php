<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class StockAdjustmentRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        int $companyId,
        ?int $productId,
        ?string $dateFrom,
        ?string $dateTo,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['a.company_id = :cid'];
        $params = ['cid' => $companyId];
        if ($productId !== null && $productId > 0) {
            $where[] = 'a.product_id = :pid';
            $params['pid'] = $productId;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 'DATE(a.created_at) >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'DATE(a.created_at) <= :dt';
            $params['dt'] = $dateTo;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM stock_adjustments a WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT a.*, p.name AS product_name, p.sku, st.name AS store_name, u.name AS user_name
                FROM stock_adjustments a
                LEFT JOIN products p ON p.id = a.product_id
                LEFT JOIN stores st ON st.id = a.store_id
                LEFT JOIN users u ON u.id = a.created_by
                WHERE {$whereSql}
                ORDER BY a.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    public function insert(int $companyId, array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO stock_adjustments (company_id, store_id, product_id, direction, qty, reason_text, notes, created_by, created_at)
             VALUES (:cid, :sid, :pid, :dir, :qty, :reason, :notes, :uid, NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'sid' => $data['store_id'],
            'pid' => $data['product_id'],
            'dir' => $data['direction'],
            'qty' => $data['qty'],
            'reason' => $data['reason_text'] ?? null,
            'notes' => $data['notes'] ?? null,
            'uid' => $data['created_by'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT a.*, p.name AS product_name, st.name AS store_name, u.name AS user_name
             FROM stock_adjustments a
             LEFT JOIN products p ON p.id = a.product_id
             LEFT JOIN stores st ON st.id = a.store_id
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id = :id AND a.company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }
}
