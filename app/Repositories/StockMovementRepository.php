<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class StockMovementRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        int $companyId,
        string $search,
        string $typeFilter,
        ?int $productId,
        ?int $storeId,
        ?string $dateFrom,
        ?string $dateTo,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['sm.company_id = :cid'];
        $params = ['cid' => $companyId];
        if ($typeFilter !== '' && $typeFilter !== 'all') {
            $where[] = 'sm.movement_type = :mt';
            $params['mt'] = $typeFilter;
        }
        if ($productId !== null && $productId > 0) {
            $where[] = 'sm.product_id = :pid';
            $params['pid'] = $productId;
        }
        if ($storeId !== null && $storeId > 0) {
            $where[] = 'sm.store_id = :sid';
            $params['sid'] = $storeId;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 'DATE(sm.created_at) >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'DATE(sm.created_at) <= :dt';
            $params['dt'] = $dateTo;
        }
        if ($search !== '') {
            $where[] = '(sm.reference LIKE :q OR sm.notes LIKE :q2 OR p.name LIKE :q3 OR p.sku LIKE :q4)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
            $params['q4'] = $w;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM stock_movements sm
             LEFT JOIN products p ON p.id = sm.product_id
             WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT sm.*, p.name AS product_name, p.sku, st.name AS store_name, u.name AS user_name
                FROM stock_movements sm
                LEFT JOIN products p ON p.id = sm.product_id
                LEFT JOIN stores st ON st.id = sm.store_id
                LEFT JOIN users u ON u.id = sm.created_by
                WHERE {$whereSql}
                ORDER BY sm.id DESC
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
            'SELECT sm.*, p.name AS product_name, p.sku, st.name AS store_name, u.name AS user_name
             FROM stock_movements sm
             LEFT JOIN products p ON p.id = sm.product_id
             LEFT JOIN stores st ON st.id = sm.store_id
             LEFT JOIN users u ON u.id = sm.created_by
             WHERE sm.id = :id AND sm.company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }
}
