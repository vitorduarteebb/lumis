<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\AccountPayableRepository;
use App\Repositories\ProductStoreStockRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\StockTransferRepository;
use PDO;

final class EstoqueOperacaoService
{
    public function __construct(
        private PDO $pdo,
        private ProductStoreStockRepository $pssRepo,
        private InventoryStockService $stockService,
        private PurchaseOrderRepository $poRepo,
        private StockTransferRepository $transferRepo,
        private AccountPayableRepository $apRepo
    ) {
    }

    public static function make(): self
    {
        $pdo = Database::connection();
        $pss = new ProductStoreStockRepository();

        return new self(
            $pdo,
            $pss,
            new InventoryStockService($pdo, $pss),
            new PurchaseOrderRepository(),
            new StockTransferRepository(),
            new AccountPayableRepository()
        );
    }

    public function finalizePurchase(int $poId, int $companyId, int $userId, bool $createAp, ?string $apDueDate): void
    {
        $bundle = $this->poRepo->findWithLines($poId, $companyId);
        if ($bundle === null) {
            throw new \RuntimeException('Compra não encontrada.');
        }
        $po = $bundle['order'];
        if (($po['status'] ?? '') !== 'open') {
            throw new \RuntimeException('Somente compras em aberto podem ser finalizadas.');
        }
        $storeId = isset($po['store_id']) && $po['store_id'] !== null ? (int) $po['store_id'] : $this->pssRepo->defaultStoreId($companyId);
        if ($storeId === null) {
            throw new \RuntimeException('Defina a loja de destino ou cadastre uma loja.');
        }

        $this->pdo->beginTransaction();
        try {
            foreach ($bundle['lines'] as $ln) {
                $pid = (int) $ln['product_id'];
                $qty = (float) $ln['qty'];
                if ($qty <= 0) {
                    continue;
                }
                $this->stockService->applyMovement(
                    $companyId,
                    $storeId,
                    $pid,
                    $qty,
                    'purchase',
                    'purchase_orders',
                    $poId,
                    $userId,
                    'Entrada por compra finalizada',
                    'OC ' . (string) ($po['document_number'] ?? $poId)
                );
            }
            $this->poRepo->setStatus($poId, $companyId, 'finalized');

            if ($createAp && (float) ($po['total_amount'] ?? 0) > 0) {
                $sid = isset($po['supplier_id']) ? (int) $po['supplier_id'] : null;
                $due = $apDueDate ?? date('Y-m-d', strtotime('+30 days'));
                $apId = $this->apRepo->insert($companyId, [
                    'supplier_id' => $sid,
                    'description' => 'Compra ' . (string) ($po['document_number'] ?? ('#' . $poId)),
                    'amount' => (float) $po['total_amount'],
                    'due_date' => $due,
                    'status' => 'open',
                ]);
                $this->poRepo->linkAccountsPayable($poId, $apId);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function completeTransfer(int $transferId, int $companyId, int $userId): void
    {
        $bundle = $this->transferRepo->findWithItems($transferId, $companyId);
        if ($bundle === null) {
            throw new \RuntimeException('Transferência não encontrada.');
        }
        $t = $bundle['transfer'];
        if (($t['status'] ?? '') !== 'pending') {
            throw new \RuntimeException('Somente transferências pendentes podem ser concluídas.');
        }
        $from = (int) $t['from_store_id'];
        $to = (int) $t['to_store_id'];

        $this->pdo->beginTransaction();
        try {
            foreach ($bundle['items'] as $it) {
                $pid = (int) $it['product_id'];
                $qty = (float) $it['qty'];
                if ($qty <= 0) {
                    continue;
                }
                $avail = $this->pssRepo->getQty($companyId, $from, $pid);
                if ($avail + 0.0001 < $qty) {
                    throw new \RuntimeException('Saldo insuficiente na origem para ' . (string) ($it['product_name'] ?? 'produto') . '.');
                }
                $this->stockService->applyMovement(
                    $companyId,
                    $from,
                    $pid,
                    -$qty,
                    'transfer_out',
                    'stock_transfers',
                    $transferId,
                    $userId,
                    'Saída por transferência',
                    'TR-' . $transferId
                );
                $this->stockService->applyMovement(
                    $companyId,
                    $to,
                    $pid,
                    $qty,
                    'transfer_in',
                    'stock_transfers',
                    $transferId,
                    $userId,
                    'Entrada por transferência',
                    'TR-' . $transferId
                );
            }
            $this->transferRepo->setStatus($transferId, $companyId, 'done');
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Aplica efeito no estoque conforme tipo de devolução/troca.
     */
    public function applyReturnStock(int $returnId, int $companyId, string $returnKind, int $storeId, int $productId, float $qty): void
    {
        if ($qty <= 0) {
            return;
        }
        $delta = match ($returnKind) {
            'sale_return', 'exchange' => $qty,
            'purchase_return' => -$qty,
            default => $qty,
        };
        $type = match ($returnKind) {
            'sale_return', 'exchange' => 'return_sale',
            'purchase_return' => 'return_purchase',
            default => 'adjust',
        };
        $this->stockService->applyMovement(
            $companyId,
            $storeId,
            $productId,
            $delta,
            $type,
            'stock_returns',
            $returnId,
            null,
            'Devolução/troca registrada',
            'RET-' . $returnId
        );
    }
}
