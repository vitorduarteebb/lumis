<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Vendas mínimas a partir de orçamento (cabeçalho + linhas).
 */
final class SalesDocumentRepository extends BaseRepository
{
    /**
     * @param list<array{product_id?: int|null, service_id?: int|null, description?: string|null, qty: float, unit_price: float, line_total: float}> $lines
     */
    public function createFromQuote(
        int $companyId,
        ?int $clientId,
        float $totalAmount,
        array $lines,
        ?int $createdBy = null
    ): int {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO sales_documents (company_id, client_id, document_number, total_amount, status, issued_at, created_at, updated_at)
             VALUES (:cid, :clid, :dnum, :tot, :st, NOW(), NOW(), NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'clid' => $clientId,
            'dnum' => null,
            'tot' => $totalAmount,
            'st' => 'finalized',
        ]);
        $docId = (int) $pdo->lastInsertId();
        $num = sprintf('VD-%d', $docId);
        $pdo->prepare('UPDATE sales_documents SET document_number = :n WHERE id = :id')->execute(['n' => $num, 'id' => $docId]);

        $ins = $pdo->prepare(
            'INSERT INTO sales_document_lines (sales_document_id, product_id, service_id, description, qty, unit_price, line_total)
             VALUES (:did, :pid, :sid, :desc, :qty, :up, :lt)'
        );
        foreach ($lines as $ln) {
            $ins->execute([
                'did' => $docId,
                'pid' => $ln['product_id'] ?? null,
                'sid' => $ln['service_id'] ?? null,
                'desc' => $ln['description'] ?? null,
                'qty' => $ln['qty'],
                'up' => $ln['unit_price'],
                'lt' => $ln['line_total'],
            ]);
        }

        return $docId;
    }
}
