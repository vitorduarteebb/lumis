<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SupplierQuoteRepository extends BaseRepository
{
    public function paginate(int $companyId, string $search, string $status, ?string $df, ?string $dt, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['q.company_id = :cid', 'q.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($status !== '' && $status !== 'all') {
            $where[] = 'q.status = :st';
            $params['st'] = $status;
        }
        if ($search !== '') {
            $where[] = '(q.quote_number LIKE :s OR sup.name LIKE :s2 OR sup.trade_name LIKE :s2 OR q.notes LIKE :s3)';
            $w = '%' . $search . '%';
            $params['s'] = $w;
            $params['s2'] = $w;
            $params['s3'] = $w;
        }
        if ($df !== null && $df !== '') {
            $where[] = 'q.quoted_at >= :df';
            $params['df'] = $df;
        }
        if ($dt !== null && $dt !== '') {
            $where[] = 'q.quoted_at <= :dt';
            $params['dt'] = $dt;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM supplier_quotes q LEFT JOIN suppliers sup ON sup.id = q.supplier_id WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT q.*, sup.trade_name AS supplier_name, sup.legal_name AS supplier_legal
                FROM supplier_quotes q
                LEFT JOIN suppliers sup ON sup.id = q.supplier_id
                WHERE {$whereSql}
                ORDER BY q.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @param list<array{product_id: int, qty: float, unit_cost: float, line_total: float}> $lines
     */
    public function create(int $companyId, array $header, array $lines, ?int $userId): int
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO supplier_quotes (company_id, supplier_id, quote_number, status, quoted_at, notes, total_amount, created_by, created_at)
             VALUES (:cid, :sid, :num, :st, :qd, :notes, :tot, :uid, NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'sid' => $header['supplier_id'],
            'num' => $header['quote_number'] ?? null,
            'st' => $header['status'] ?? 'open',
            'qd' => $header['quoted_at'],
            'notes' => $header['notes'] ?? null,
            'tot' => $header['total_amount'] ?? 0,
            'uid' => $userId,
        ]);
        $id = (int) $pdo->lastInsertId();
        if ($header['quote_number'] === null || $header['quote_number'] === '') {
            $pdo->prepare('UPDATE supplier_quotes SET quote_number = :n WHERE id = :id')->execute([
                'n' => 'COT-' . $id,
                'id' => $id,
            ]);
        }
        $ins = $pdo->prepare(
            'INSERT INTO supplier_quote_items (supplier_quote_id, product_id, qty, unit_cost, line_total)
             VALUES (:qid, :pid, :qty, :uc, :lt)'
        );
        foreach ($lines as $ln) {
            $ins->execute([
                'qid' => $id,
                'pid' => $ln['product_id'],
                'qty' => $ln['qty'],
                'uc' => $ln['unit_cost'],
                'lt' => $ln['line_total'],
            ]);
        }

        return $id;
    }

    public function findWithLines(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT q.*, sup.trade_name AS supplier_name FROM supplier_quotes q
             LEFT JOIN suppliers sup ON sup.id = q.supplier_id
             WHERE q.id = :id AND q.company_id = :cid AND q.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $q = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($q === false) {
            return null;
        }
        $stmt = $this->pdo()->prepare(
            'SELECT i.*, p.name AS product_name, p.sku FROM supplier_quote_items i
             LEFT JOIN products p ON p.id = i.product_id WHERE i.supplier_quote_id = :id ORDER BY i.id'
        );
        $stmt->execute(['id' => $id]);

        return ['quote' => $q, 'lines' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function updateHeader(int $id, int $companyId, array $header, ?int $userId): void
    {
        $this->pdo()->prepare(
            'UPDATE supplier_quotes SET supplier_id = :sid, quote_number = :num, status = :st, quoted_at = :qd, notes = :notes, total_amount = :tot, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        )->execute([
            'sid' => $header['supplier_id'],
            'num' => $header['quote_number'],
            'st' => $header['status'] ?? 'open',
            'qd' => $header['quoted_at'],
            'notes' => $header['notes'] ?? null,
            'tot' => $header['total_amount'] ?? 0,
            'id' => $id,
            'cid' => $companyId,
        ]);
    }

    public function replaceLines(int $quoteId, array $lines): void
    {
        $this->pdo()->prepare('DELETE FROM supplier_quote_items WHERE supplier_quote_id = :id')->execute(['id' => $quoteId]);
        $ins = $this->pdo()->prepare(
            'INSERT INTO supplier_quote_items (supplier_quote_id, product_id, qty, unit_cost, line_total) VALUES (:qid, :pid, :qty, :uc, :lt)'
        );
        foreach ($lines as $ln) {
            $ins->execute([
                'qid' => $quoteId,
                'pid' => $ln['product_id'],
                'qty' => $ln['qty'],
                'uc' => $ln['unit_cost'],
                'lt' => $ln['line_total'],
            ]);
        }
    }

    public function softDelete(int $id, int $companyId): void
    {
        $this->pdo()->prepare(
            'UPDATE supplier_quotes SET deleted_at = NOW(), status = \'cancelled\' WHERE id = :id AND company_id = :cid'
        )->execute(['id' => $id, 'cid' => $companyId]);
    }
}
