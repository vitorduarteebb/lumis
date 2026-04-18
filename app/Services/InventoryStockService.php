<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ProductStoreStockRepository;
use PDO;

/**
 * Movimentações de estoque com saldo por loja (product_store_stock) e total em products.stock_qty.
 */
final class InventoryStockService
{
    public function __construct(
        private PDO $pdo,
        private ProductStoreStockRepository $pssRepo
    ) {
    }

    /**
     * @param float $signedQty positivo = entrada, negativo = saída
     */
    public function applyMovement(
        int $companyId,
        int $storeId,
        int $productId,
        float $signedQty,
        string $movementType,
        ?string $refTable,
        ?int $refId,
        ?int $userId,
        ?string $notes,
        ?string $reference = null
    ): void {
        if (abs($signedQty) < 0.0000001) {
            return;
        }
        $this->pssRepo->ensureRow($companyId, $storeId, $productId);
        $before = $this->pssRepo->getQty($companyId, $storeId, $productId);
        $after = $before + $signedQty;
        if ($after < -0.0000001) {
            throw new \RuntimeException('Saldo insuficiente no depósito para esta operação.');
        }
        $stmt = $this->pdo->prepare(
            'UPDATE product_store_stock SET qty = qty + :dq WHERE company_id = :cid AND store_id = :sid AND product_id = :pid'
        );
        $stmt->execute([
            'dq' => $signedQty,
            'cid' => $companyId,
            'sid' => $storeId,
            'pid' => $productId,
        ]);
        $this->pssRepo->syncProductTotalQty($productId);
        $stmt = $this->pdo->prepare(
            'INSERT INTO stock_movements (company_id, product_id, store_id, movement_type, qty, balance_before, balance_after, reference, notes, ref_table, ref_id, created_by, created_at)
             VALUES (:cid, :pid, :sid, :mt, :qty, :bb, :ba, :ref, :notes, :rtab, :rid, :uid, NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'pid' => $productId,
            'sid' => $storeId,
            'mt' => $movementType,
            'qty' => $signedQty,
            'bb' => $before,
            'ba' => $after,
            'ref' => $reference,
            'notes' => $notes,
            'rtab' => $refTable,
            'rid' => $refId,
            'uid' => $userId,
        ]);
    }
}
