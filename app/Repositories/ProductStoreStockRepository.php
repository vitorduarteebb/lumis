<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductStoreStockRepository extends BaseRepository
{
    public function getQty(int $companyId, int $storeId, int $productId): float
    {
        $stmt = $this->pdo()->prepare(
            'SELECT qty FROM product_store_stock WHERE company_id = :cid AND store_id = :sid AND product_id = :pid LIMIT 1'
        );
        $stmt->execute(['cid' => $companyId, 'sid' => $storeId, 'pid' => $productId]);
        $v = $stmt->fetchColumn();

        return $v !== false ? (float) $v : 0.0;
    }

    public function ensureRow(int $companyId, int $storeId, int $productId): void
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO product_store_stock (company_id, store_id, product_id, qty) VALUES (:cid, :sid, :pid, 0)'
        );
        $stmt->execute(['cid' => $companyId, 'sid' => $storeId, 'pid' => $productId]);
    }

    /**
     * @return int|null ID da primeira loja ativa ou null
     */
    public function defaultStoreId(int $companyId): ?int
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id FROM stores WHERE company_id = :cid AND status = 1 ORDER BY id ASC LIMIT 1'
        );
        $stmt->execute(['cid' => $companyId]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    public function syncProductTotalQty(int $productId): void
    {
        $this->pdo()->prepare(
            'UPDATE products p SET stock_qty = (
                SELECT COALESCE(SUM(qty), 0) FROM product_store_stock WHERE product_id = p.id
            ) WHERE p.id = :pid'
        )->execute(['pid' => $productId]);
    }
}
