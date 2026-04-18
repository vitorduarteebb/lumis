<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class FiscalDocumentRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        int $companyId,
        string $documentKind,
        string $search,
        string $status,
        ?int $storeId,
        ?string $dateFrom,
        ?string $dateTo,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['fd.company_id = :cid', 'fd.deleted_at IS NULL', 'fd.document_kind = :dk'];
        $params = ['cid' => $companyId, 'dk' => $documentKind];
        if ($status !== '' && $status !== 'all') {
            $where[] = 'fd.status = :st';
            $params['st'] = $status;
        }
        if ($storeId !== null && $storeId > 0) {
            $where[] = 'fd.store_id = :sid';
            $params['sid'] = $storeId;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 'DATE(COALESCE(fd.issued_at, fd.created_at)) >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'DATE(COALESCE(fd.issued_at, fd.created_at)) <= :dt';
            $params['dt'] = $dateTo;
        }
        if ($search !== '') {
            $where[] = '(fd.document_number LIKE :sq OR fd.series LIKE :sq OR fd.access_key LIKE :sq OR fd.notes LIKE :sq OR c.name LIKE :sq OR sup.trade_name LIKE :sq)';
            $params['sq'] = '%' . $search . '%';
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM fiscal_documents fd
             LEFT JOIN clients c ON c.id = fd.client_id
             LEFT JOIN suppliers sup ON sup.id = fd.supplier_id
             WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT fd.*, c.name AS client_name, sup.trade_name AS supplier_name, st.name AS store_name
                FROM fiscal_documents fd
                LEFT JOIN clients c ON c.id = fd.client_id
                LEFT JOIN suppliers sup ON sup.id = fd.supplier_id
                LEFT JOIN stores st ON st.id = fd.store_id
                WHERE {$whereSql}
                ORDER BY fd.id DESC
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
            'SELECT fd.*, c.name AS client_name, sup.trade_name AS supplier_name, st.name AS store_name
             FROM fiscal_documents fd
             LEFT JOIN clients c ON c.id = fd.client_id
             LEFT JOIN suppliers sup ON sup.id = fd.supplier_id
             LEFT JOIN stores st ON st.id = fd.store_id
             WHERE fd.id = :id AND fd.company_id = :cid AND fd.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($doc === false) {
            return null;
        }
        $stmt = $this->pdo()->prepare(
            'SELECT l.*, p.name AS product_name, sv.name AS service_name
             FROM fiscal_document_lines l
             LEFT JOIN products p ON p.id = l.product_id
             LEFT JOIN services sv ON sv.id = l.service_id
             WHERE l.fiscal_document_id = :did ORDER BY l.sort_order ASC, l.id ASC'
        );
        $stmt->execute(['did' => $id]);

        return ['doc' => $doc, 'lines' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    /**
     * @param list<array{product_id?: int|null, service_id?: int|null, description?: string|null, qty: float, unit_price: float, line_discount?: float, line_total: float}> $lines
     */
    public function create(int $companyId, string $documentKind, array $header, array $lines, ?int $userId): int
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO fiscal_documents (
                company_id, store_id, document_kind, client_id, supplier_id, document_number, series, access_key, issued_at, status,
                subtotal_amount, discount_total, total_amount, notes, xml_path, pdf_path, purchase_order_id,
                nature_entry_id, cfop_entry_id, model_entry_id, series_entry_id, created_by, created_at, updated_at
            ) VALUES (
                :cid, :sid, :dk, :clid, :sup, :dnum, :ser, :akey, :iss, :st,
                :sub, :disc, :tot, :notes, :xp, :pp, :poid,
                :nat, :cfop, :mod, :sere, :cb, NOW(), NOW()
            )'
        );
        $stmt->execute([
            'cid' => $companyId,
            'sid' => $header['store_id'] ?? null,
            'dk' => $documentKind,
            'clid' => $header['client_id'] ?? null,
            'sup' => $header['supplier_id'] ?? null,
            'dnum' => $header['document_number'] ?? null,
            'ser' => $header['series'] ?? null,
            'akey' => $header['access_key'] ?? null,
            'iss' => $header['issued_at'] ?? null,
            'st' => $header['status'] ?? 'draft',
            'sub' => $header['subtotal_amount'] ?? 0,
            'disc' => $header['discount_total'] ?? 0,
            'tot' => $header['total_amount'] ?? 0,
            'notes' => $header['notes'] ?? null,
            'xp' => $header['xml_path'] ?? null,
            'pp' => $header['pdf_path'] ?? null,
            'poid' => $header['purchase_order_id'] ?? null,
            'nat' => $header['nature_entry_id'] ?? null,
            'cfop' => $header['cfop_entry_id'] ?? null,
            'mod' => $header['model_entry_id'] ?? null,
            'sere' => $header['series_entry_id'] ?? null,
            'cb' => $userId,
        ]);
        $id = (int) $pdo->lastInsertId();
        $this->replaceLines($id, $lines);

        return $id;
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    public function updateOpen(int $id, int $companyId, array $header, array $lines, ?int $userId): void
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'UPDATE fiscal_documents SET
                store_id = :sid, client_id = :clid, supplier_id = :sup, document_number = :dnum, series = :ser, access_key = :akey,
                issued_at = :iss, status = :st, subtotal_amount = :sub, discount_total = :disc, total_amount = :tot, notes = :notes,
                xml_path = COALESCE(:xp, xml_path), pdf_path = COALESCE(:pp, pdf_path), purchase_order_id = :poid,
                nature_entry_id = :nat, cfop_entry_id = :cfop, model_entry_id = :mod, series_entry_id = :sere,
                updated_at = NOW(), updated_by = :ub
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL AND status IN (\'draft\', \'error\')'
        );
        $stmt->execute([
            'id' => $id,
            'cid' => $companyId,
            'sid' => $header['store_id'] ?? null,
            'clid' => $header['client_id'] ?? null,
            'sup' => $header['supplier_id'] ?? null,
            'dnum' => $header['document_number'] ?? null,
            'ser' => $header['series'] ?? null,
            'akey' => $header['access_key'] ?? null,
            'iss' => $header['issued_at'] ?? null,
            'st' => $header['status'] ?? 'draft',
            'sub' => $header['subtotal_amount'] ?? 0,
            'disc' => $header['discount_total'] ?? 0,
            'tot' => $header['total_amount'] ?? 0,
            'notes' => $header['notes'] ?? null,
            'xp' => $header['xml_path'] ?? null,
            'pp' => $header['pdf_path'] ?? null,
            'poid' => $header['purchase_order_id'] ?? null,
            'nat' => $header['nature_entry_id'] ?? null,
            'cfop' => $header['cfop_entry_id'] ?? null,
            'mod' => $header['model_entry_id'] ?? null,
            'sere' => $header['series_entry_id'] ?? null,
            'ub' => $userId,
        ]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Nota não encontrada ou não editável.');
        }
        $this->replaceLines($id, $lines);
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    private function replaceLines(int $documentId, array $lines): void
    {
        $this->pdo()->prepare('DELETE FROM fiscal_document_lines WHERE fiscal_document_id = :id')->execute(['id' => $documentId]);
        $ins = $this->pdo()->prepare(
            'INSERT INTO fiscal_document_lines (fiscal_document_id, product_id, service_id, description, qty, unit_price, line_discount, line_total, sort_order)
             VALUES (:did, :pid, :sid, :desc, :qty, :up, :ld, :lt, :so)'
        );
        $so = 0;
        foreach ($lines as $ln) {
            $ins->execute([
                'did' => $documentId,
                'pid' => $ln['product_id'] ?? null,
                'sid' => $ln['service_id'] ?? null,
                'desc' => $ln['description'] ?? null,
                'qty' => $ln['qty'],
                'up' => $ln['unit_price'],
                'ld' => $ln['line_discount'] ?? 0,
                'lt' => $ln['line_total'],
                'so' => $so++,
            ]);
        }
    }

    public function setStatus(int $id, int $companyId, string $status, ?int $userId): void
    {
        $this->pdo()->prepare(
            'UPDATE fiscal_documents SET status = :st, updated_at = NOW(), updated_by = :ub WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        )->execute(['st' => $status, 'ub' => $userId, 'id' => $id, 'cid' => $companyId]);
    }

    public function updatePaths(int $id, int $companyId, ?string $xmlPath, ?string $pdfPath, ?int $userId): void
    {
        $this->pdo()->prepare(
            'UPDATE fiscal_documents SET xml_path = COALESCE(:xp, xml_path), pdf_path = COALESCE(:pp, pdf_path), updated_at = NOW(), updated_by = :ub
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        )->execute(['xp' => $xmlPath, 'pp' => $pdfPath, 'ub' => $userId, 'id' => $id, 'cid' => $companyId]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $this->pdo()->prepare(
            'UPDATE fiscal_documents SET deleted_at = NOW(), status = \'cancelled\' WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        )->execute(['id' => $id, 'cid' => $companyId]);
    }
}
