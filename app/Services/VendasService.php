<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\AccountReceivableRepository;
use App\Repositories\ProductStoreStockRepository;
use App\Repositories\SalesDocumentRepository;
use PDO;

final class VendasService
{
    public function __construct(
        private PDO $pdo,
        private SalesDocumentRepository $salesRepo,
        private ProductStoreStockRepository $pssRepo,
        private InventoryStockService $stockService,
        private AccountReceivableRepository $arRepo
    ) {
    }

    public static function make(): self
    {
        $pdo = Database::connection();

        return new self(
            $pdo,
            new SalesDocumentRepository(),
            new ProductStoreStockRepository(),
            new InventoryStockService($pdo, new ProductStoreStockRepository()),
            new AccountReceivableRepository()
        );
    }

    /**
     * Finaliza venda: baixa estoque (produto/balcão), atualiza status, opcional conta a receber.
     */
    public function finalize(int $docId, int $companyId, int $userId, bool $createAccountsReceivable, ?string $receivableDueDate): void
    {
        $bundle = $this->salesRepo->findWithLines($docId, $companyId);
        if ($bundle === null) {
            throw new \RuntimeException('Venda não encontrada.');
        }
        $doc = $bundle['doc'];
        if (($doc['status'] ?? '') !== 'open') {
            throw new \RuntimeException('Somente vendas em aberto podem ser finalizadas.');
        }
        $kind = (string) ($doc['document_kind'] ?? 'product');
        $storeId = isset($doc['store_id']) && $doc['store_id'] !== null ? (int) $doc['store_id'] : $this->pssRepo->defaultStoreId($companyId);
        if ($storeId === null) {
            throw new \RuntimeException('Cadastre pelo menos uma loja em Configurações para movimentar estoque.');
        }

        $this->pdo->beginTransaction();
        try {
            if ($kind === 'product' || $kind === 'balcao') {
                foreach ($bundle['lines'] as $ln) {
                    $pid = isset($ln['product_id']) ? (int) $ln['product_id'] : 0;
                    if ($pid <= 0) {
                        continue;
                    }
                    $qty = (float) $ln['qty'];
                    if ($qty <= 0) {
                        continue;
                    }
                    $avail = $this->pssRepo->getQty($companyId, $storeId, $pid);
                    if ($avail + 0.0001 < $qty) {
                        throw new \RuntimeException(
                            'Estoque insuficiente para o produto na linha (disponível: ' . number_format($avail, 4, ',', '.') . ').'
                        );
                    }
                    $this->stockService->applyMovement(
                        $companyId,
                        $storeId,
                        $pid,
                        -$qty,
                        'sale',
                        'sales_documents',
                        $docId,
                        $userId,
                        'Baixa por venda finalizada',
                        'Venda #' . (string) ($doc['document_number'] ?? $docId)
                    );
                }
            }

            $this->salesRepo->setStatus($docId, $companyId, 'finalized', $userId);

            if ($createAccountsReceivable && (float) ($doc['total_amount'] ?? 0) > 0) {
                $clientId = isset($doc['client_id']) && $doc['client_id'] !== null ? (int) $doc['client_id'] : null;
                if ($clientId !== null) {
                    $due = $receivableDueDate ?? date('Y-m-d', strtotime('+30 days'));
                    $arId = $this->arRepo->insert($companyId, [
                        'client_id' => $clientId,
                        'description' => 'Venda ' . (string) ($doc['document_number'] ?? ('#' . $docId)),
                        'amount' => (float) $doc['total_amount'],
                        'due_date' => $due,
                        'status' => 'open',
                    ]);
                    $this->salesRepo->linkAccountsReceivable($docId, $arId);
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Cancela venda em aberto (sem estoque) ou finalizada (estorna estoque).
     */
    public function cancel(int $docId, int $companyId, int $userId): void
    {
        $bundle = $this->salesRepo->findWithLines($docId, $companyId);
        if ($bundle === null) {
            throw new \RuntimeException('Venda não encontrada.');
        }
        $doc = $bundle['doc'];
        $st = (string) ($doc['status'] ?? '');
        if ($st === 'cancelled') {
            throw new \RuntimeException('Venda já cancelada.');
        }

        $this->pdo->beginTransaction();
        try {
            if ($st === 'finalized') {
                $kind = (string) ($doc['document_kind'] ?? 'product');
                $storeId = isset($doc['store_id']) && $doc['store_id'] !== null ? (int) $doc['store_id'] : $this->pssRepo->defaultStoreId($companyId);
                if ($storeId !== null && ($kind === 'product' || $kind === 'balcao')) {
                    foreach ($bundle['lines'] as $ln) {
                        $pid = isset($ln['product_id']) ? (int) $ln['product_id'] : 0;
                        if ($pid <= 0) {
                            continue;
                        }
                        $qty = (float) $ln['qty'];
                        if ($qty <= 0) {
                            continue;
                        }
                        $this->stockService->applyMovement(
                            $companyId,
                            $storeId,
                            $pid,
                            $qty,
                            'return_sale',
                            'sales_documents',
                            $docId,
                            $userId,
                            'Estorno por cancelamento de venda',
                            'Estorno #' . (string) ($doc['document_number'] ?? $docId)
                        );
                    }
                }
            }
            $this->pdo->prepare(
                'UPDATE sales_documents SET status = \'cancelled\', updated_at = NOW(), updated_by = :u WHERE id = :id AND company_id = :cid'
            )->execute(['u' => $userId, 'id' => $docId, 'cid' => $companyId]);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
