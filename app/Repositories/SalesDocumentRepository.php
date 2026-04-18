<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Vendas (sales_documents + linhas): produto, balcão e serviço.
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
            'INSERT INTO sales_documents (company_id, client_id, document_number, total_amount, status, document_kind, issued_at, created_at, updated_at, created_by)
             VALUES (:cid, :clid, :dnum, :tot, :st, \'product\', NOW(), NOW(), NOW(), :cb)'
        );
        $stmt->execute([
            'cid' => $companyId,
            'clid' => $clientId,
            'dnum' => null,
            'tot' => $totalAmount,
            'st' => 'finalized',
            'cb' => $createdBy,
        ]);
        $docId = (int) $pdo->lastInsertId();
        $num = sprintf('VP-%d', $docId);
        $pdo->prepare('UPDATE sales_documents SET document_number = :n WHERE id = :id')->execute(['n' => $num, 'id' => $docId]);

        $this->insertLines($docId, $lines);

        return $docId;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        int $companyId,
        string $documentKind,
        string $search,
        string $status,
        ?string $dateFrom,
        ?string $dateTo,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['sd.company_id = :cid', 'sd.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($documentKind === 'product') {
            $where[] = "sd.document_kind IN ('product', 'balcao')";
        } else {
            $where[] = 'sd.document_kind = :dk';
            $params['dk'] = $documentKind;
        }
        if ($status !== '' && $status !== 'all') {
            $where[] = 'sd.status = :st';
            $params['st'] = $status;
        }
        if ($search !== '') {
            $where[] = '(sd.document_number LIKE :q OR c.name LIKE :q2 OR sd.notes LIKE :q3)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 'DATE(COALESCE(sd.issued_at, sd.created_at)) >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'DATE(COALESCE(sd.issued_at, sd.created_at)) <= :dt';
            $params['dt'] = $dateTo;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM sales_documents sd
             LEFT JOIN clients c ON c.id = sd.client_id
             WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT sd.*, c.name AS client_name, u.name AS seller_name
                FROM sales_documents sd
                LEFT JOIN clients c ON c.id = sd.client_id
                LEFT JOIN users u ON u.id = sd.seller_user_id
                WHERE {$whereSql}
                ORDER BY sd.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array{doc: array<string, mixed>, lines: list<array<string, mixed>>}|null
     */
    public function findWithLines(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT sd.*, c.name AS client_name, c.email AS client_email, u.name AS seller_name
             FROM sales_documents sd
             LEFT JOIN clients c ON c.id = sd.client_id
             LEFT JOIN users u ON u.id = sd.seller_user_id
             WHERE sd.id = :id AND sd.company_id = :cid AND sd.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($doc === false) {
            return null;
        }
        $stmt = $this->pdo()->prepare(
            'SELECT sdl.*, p.name AS product_name, p.sku AS product_sku, s.name AS service_name
             FROM sales_document_lines sdl
             LEFT JOIN products p ON p.id = sdl.product_id
             LEFT JOIN services s ON s.id = sdl.service_id
             WHERE sdl.sales_document_id = :did ORDER BY sdl.id ASC'
        );
        $stmt->execute(['did' => $id]);
        $lines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['doc' => $doc, 'lines' => $lines];
    }

    /**
     * @param list<array{product_id?: int|null, service_id?: int|null, description?: string|null, qty: float, unit_price: float, line_discount?: float, line_total: float}> $lines
     */
    public function createDraft(
        int $companyId,
        string $documentKind,
        array $header,
        array $lines,
        ?int $userId
    ): int {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO sales_documents (
                company_id, client_id, store_id, seller_user_id, document_kind, document_number,
                status, notes, subtotal_amount, discount_total, total_amount,
                payment_method_entry_id, payment_terms_entry_id, sale_channel_entry_id,
                issued_at, created_at, updated_at, created_by
            ) VALUES (
                :cid, :clid, :sid, :sell, :dk, NULL,
                \'open\', :notes, :sub, :disc, :tot,
                :pme, :pte, :che,
                NULL, NOW(), NOW(), :cb
            )'
        );
        $stmt->execute([
            'cid' => $companyId,
            'clid' => $header['client_id'] ?? null,
            'sid' => $header['store_id'] ?? null,
            'sell' => $header['seller_user_id'] ?? null,
            'dk' => $documentKind,
            'notes' => $header['notes'] ?? null,
            'sub' => $header['subtotal_amount'] ?? 0,
            'disc' => $header['discount_total'] ?? 0,
            'tot' => $header['total_amount'] ?? 0,
            'pme' => $header['payment_method_entry_id'] ?? null,
            'pte' => $header['payment_terms_entry_id'] ?? null,
            'che' => $header['sale_channel_entry_id'] ?? null,
            'cb' => $userId,
        ]);
        $docId = (int) $pdo->lastInsertId();
        $prefix = match ($documentKind) {
            'service' => 'VS',
            'balcao' => 'VB',
            default => 'VP',
        };
        $num = sprintf('%s-%d', $prefix, $docId);
        $pdo->prepare('UPDATE sales_documents SET document_number = :n WHERE id = :id')->execute(['n' => $num, 'id' => $docId]);
        $this->insertLines($docId, $lines);

        return $docId;
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    public function updateOpenDocument(int $id, int $companyId, array $header, array $lines, ?int $userId): void
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'UPDATE sales_documents SET
                client_id = :clid, store_id = :sid, seller_user_id = :sell, notes = :notes,
                subtotal_amount = :sub, discount_total = :disc, total_amount = :tot,
                payment_method_entry_id = :pme, payment_terms_entry_id = :pte, sale_channel_entry_id = :che,
                updated_at = NOW(), updated_by = :ub
             WHERE id = :id AND company_id = :cid AND status = \'open\' AND deleted_at IS NULL'
        );
        $stmt->execute([
            'id' => $id,
            'cid' => $companyId,
            'clid' => $header['client_id'] ?? null,
            'sid' => $header['store_id'] ?? null,
            'sell' => $header['seller_user_id'] ?? null,
            'notes' => $header['notes'] ?? null,
            'sub' => $header['subtotal_amount'] ?? 0,
            'disc' => $header['discount_total'] ?? 0,
            'tot' => $header['total_amount'] ?? 0,
            'pme' => $header['payment_method_entry_id'] ?? null,
            'pte' => $header['payment_terms_entry_id'] ?? null,
            'che' => $header['sale_channel_entry_id'] ?? null,
            'ub' => $userId,
        ]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Venda não encontrada ou já finalizada.');
        }
        $pdo->prepare('DELETE FROM sales_document_lines WHERE sales_document_id = :id')->execute(['id' => $id]);
        $this->insertLines($id, $lines);
    }

    public function setStatus(int $id, int $companyId, string $status, ?int $userId = null): void
    {
        $sql = 'UPDATE sales_documents SET status = :st, issued_at = CASE WHEN :stf = \'finalized\' THEN COALESCE(issued_at, NOW()) ELSE issued_at END, updated_at = NOW(), updated_by = :ub WHERE id = :id AND company_id = :cid AND deleted_at IS NULL';
        $this->pdo()->prepare($sql)->execute([
            'st' => $status,
            'stf' => $status,
            'ub' => $userId,
            'id' => $id,
            'cid' => $companyId,
        ]);
    }

    public function linkAccountsReceivable(int $docId, int $arId): void
    {
        $this->pdo()->prepare('UPDATE sales_documents SET accounts_receivable_id = :ar WHERE id = :id')
            ->execute(['ar' => $arId, 'id' => $docId]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $this->pdo()->prepare(
            'UPDATE sales_documents SET deleted_at = NOW() WHERE id = :id AND company_id = :cid AND status = \'open\''
        )->execute(['id' => $id, 'cid' => $companyId]);
    }

    /**
     * @param list<array{product_id?: int|null, service_id?: int|null, description?: string|null, qty: float, unit_price: float, line_discount?: float, line_total: float}> $lines
     */
    private function insertLines(int $docId, array $lines): void
    {
        $ins = $this->pdo()->prepare(
            'INSERT INTO sales_document_lines (sales_document_id, product_id, service_id, description, qty, unit_price, line_discount, line_total)
             VALUES (:did, :pid, :sid, :desc, :qty, :up, :ld, :lt)'
        );
        foreach ($lines as $ln) {
            $ins->execute([
                'did' => $docId,
                'pid' => $ln['product_id'] ?? null,
                'sid' => $ln['service_id'] ?? null,
                'desc' => $ln['description'] ?? null,
                'qty' => $ln['qty'],
                'up' => $ln['unit_price'],
                'ld' => $ln['line_discount'] ?? 0,
                'lt' => $ln['line_total'],
            ]);
        }
    }
}
