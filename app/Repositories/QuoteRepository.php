<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class QuoteRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(
        int $companyId,
        string $quoteKind,
        string $search,
        string $status,
        ?string $dateFrom,
        ?string $dateTo,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['q.company_id = :cid', 'q.deleted_at IS NULL', 'q.quote_kind = :qk'];
        $params = ['cid' => $companyId, 'qk' => $quoteKind];
        if ($status !== '' && $status !== 'all') {
            $where[] = 'q.status = :st';
            $params['st'] = $status;
        }
        if ($search !== '') {
            $w = 'c.name LIKE :q2';
            $params['q2'] = '%' . $search . '%';
            if ($this->columnExists('quotes', 'quote_number')) {
                $w = '(q.quote_number LIKE :q OR ' . $w . ')';
                $params['q'] = '%' . $search . '%';
            }
            $where[] = $w;
        }
        $dateExpr = $this->columnExists('quotes', 'issued_at')
            ? 'COALESCE(q.issued_at, DATE(q.created_at))'
            : 'DATE(q.created_at)';
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = "{$dateExpr} >= :df";
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = "{$dateExpr} <= :dt";
            $params['dt'] = $dateTo;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM quotes q
             LEFT JOIN clients c ON c.id = q.client_id
             WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT q.*, c.name AS client_name FROM quotes q
                LEFT JOIN clients c ON c.id = q.client_id
                WHERE {$whereSql}
                ORDER BY q.created_at DESC, q.id DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT q.*, c.name AS client_name, c.email AS client_email
             FROM quotes q
             LEFT JOIN clients c ON c.id = q.client_id
             WHERE q.id = :id AND q.company_id = :cid AND q.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getItems(int $quoteId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT qi.*, p.name AS product_name, s.name AS service_name
             FROM quote_items qi
             LEFT JOIN products p ON p.id = qi.product_id
             LEFT JOIN services s ON s.id = qi.service_id
             WHERE qi.quote_id = :qid ORDER BY qi.id ASC'
        );
        $stmt->execute(['qid' => $quoteId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param list<array{product_id?: int|null, service_id?: int|null, description?: string, qty: float, unit_price: float, line_discount: float}> $lines
     */
    public function replaceItems(int $quoteId, string $quoteKind, array $lines): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM quote_items WHERE quote_id = :qid')->execute(['qid' => $quoteId]);
        $ins = $pdo->prepare(
            'INSERT INTO quote_items (quote_id, product_id, service_id, description, qty, unit_price, line_discount, line_total)
             VALUES (:qid, :pid, :sid, :desc, :qty, :up, :ldisc, :lt)'
        );
        foreach ($lines as $ln) {
            $qty = (float) ($ln['qty'] ?? 1);
            $up = (float) ($ln['unit_price'] ?? 0);
            $disc = (float) ($ln['line_discount'] ?? 0);
            $lt = max(0, $qty * $up - $disc);
            $pid = isset($ln['product_id']) && (int) $ln['product_id'] > 0 ? (int) $ln['product_id'] : null;
            $sid = isset($ln['service_id']) && (int) $ln['service_id'] > 0 ? (int) $ln['service_id'] : null;
            if ($quoteKind === 'product') {
                $sid = null;
            } else {
                $pid = null;
            }
            $ins->execute([
                'qid' => $quoteId,
                'pid' => $pid,
                'sid' => $sid,
                'desc' => $ln['description'] ?? null,
                'qty' => $qty,
                'up' => $up,
                'ldisc' => $disc,
                'lt' => $lt,
            ]);
        }
    }

    public function recalcTotals(int $quoteId): void
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COALESCE(SUM(line_total), 0) FROM quote_items WHERE quote_id = :qid'
        );
        $stmt->execute(['qid' => $quoteId]);
        $sub = (float) $stmt->fetchColumn();

        $stmt = $this->pdo()->prepare('SELECT discount_total FROM quotes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $quoteId]);
        $disc = (float) $stmt->fetchColumn();
        $total = max(0, $sub - $disc);

        $this->pdo()->prepare(
            'UPDATE quotes SET subtotal_amount = :sub, total_amount = :tot, updated_at = NOW() WHERE id = :id'
        )->execute(['sub' => $sub, 'tot' => $total, 'id' => $quoteId]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO quotes (company_id, client_id, quote_kind, quote_number, status, subtotal_amount, discount_total, total_amount,
             valid_until, issued_at, notes, created_by, created_at, updated_at)
             VALUES (:cid, :clid, :qk, :qnum, :st, 0, :dt, :tot, :vu, :iss, :notes, :cby, NOW(), NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'clid' => $data['client_id'] ?? null,
            'qk' => $data['quote_kind'],
            'qnum' => $data['quote_number'] ?? null,
            'st' => $data['status'] ?? 'open',
            'dt' => $data['discount_total'] ?? 0,
            'tot' => 0,
            'vu' => $data['valid_until'] ?? null,
            'iss' => $data['issued_at'] ?? null,
            'notes' => $data['notes'] ?? null,
            'cby' => $data['created_by'] ?? null,
        ]);
        $id = (int) $this->pdo()->lastInsertId();

        if (empty($data['quote_number'])) {
            $num = sprintf('ORC-%d', $id);
            $this->pdo()->prepare('UPDATE quotes SET quote_number = :n WHERE id = :id')->execute(['n' => $num, 'id' => $id]);
        }

        return $id;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE quotes SET client_id = :clid, status = :st, discount_total = :dt, valid_until = :vu, issued_at = :iss, notes = :notes, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute([
            'clid' => $data['client_id'] ?? null,
            'st' => $data['status'],
            'dt' => $data['discount_total'] ?? 0,
            'vu' => $data['valid_until'] ?? null,
            'iss' => $data['issued_at'] ?? null,
            'notes' => $data['notes'] ?? null,
            'id' => $id,
            'cid' => $companyId,
        ]);
    }

    public function setConversion(int $quoteId, int $companyId, int $salesDocumentId): void
    {
        $this->pdo()->prepare(
            'UPDATE quotes SET conversion_sales_document_id = :sd, status = :st, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        )->execute([
            'sd' => $salesDocumentId,
            'st' => 'converted',
            'id' => $quoteId,
            'cid' => $companyId,
        ]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $this->pdo()->prepare(
            'UPDATE quotes SET deleted_at = NOW(), status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        )->execute(['st' => 'cancelled', 'id' => $id, 'cid' => $companyId]);
    }

    public function markStatus(int $id, int $companyId, string $status): void
    {
        $this->pdo()->prepare(
            'UPDATE quotes SET status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        )->execute(['st' => $status, 'id' => $id, 'cid' => $companyId]);
    }

    public function duplicate(int $id, int $companyId, ?int $userId): ?int
    {
        $row = $this->findByIdForCompany($id, $companyId);
        if ($row === null) {
            return null;
        }
        $kind = (string) $row['quote_kind'];
        $newId = $this->insert($companyId, [
            'client_id' => $row['client_id'] !== null ? (int) $row['client_id'] : null,
            'quote_kind' => $kind,
            'quote_number' => null,
            'status' => 'open',
            'discount_total' => (float) ($row['discount_total'] ?? 0),
            'valid_until' => $row['valid_until'] ?? null,
            'issued_at' => date('Y-m-d'),
            'notes' => $row['notes'] ?? null,
            'created_by' => $userId,
        ]);
        $items = $this->getItems($id);
        $lines = [];
        foreach ($items as $it) {
            $lines[] = [
                'product_id' => $it['product_id'] !== null ? (int) $it['product_id'] : null,
                'service_id' => $it['service_id'] !== null ? (int) $it['service_id'] : null,
                'description' => $it['description'],
                'qty' => (float) $it['qty'],
                'unit_price' => (float) $it['unit_price'],
                'line_discount' => (float) ($it['line_discount'] ?? 0),
            ];
        }
        $this->replaceItems($newId, $kind, $lines);
        $this->recalcTotals($newId);

        return $newId;
    }

    /**
     * Contagem por status (painel orçamentos se necessário).
     *
     * @return array<string, int>
     */
    public function countByStatus(int $companyId, string $quoteKind): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT status, COUNT(*) AS cnt FROM quotes WHERE company_id = :cid AND deleted_at IS NULL AND quote_kind = :qk GROUP BY status'
        );
        $stmt->execute(['cid' => $companyId, 'qk' => $quoteKind]);
        $out = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[(string) $r['status']] = (int) $r['cnt'];
        }

        return $out;
    }
}
