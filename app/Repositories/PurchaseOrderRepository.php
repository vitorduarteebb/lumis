<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PurchaseOrderRepository extends BaseRepository
{
    public function paginate(int $companyId, string $search, string $status, ?string $df, ?string $dt, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['po.company_id = :cid', 'po.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($status !== '' && $status !== 'all') {
            $where[] = 'po.status = :st';
            $params['st'] = $status;
        }
        if ($search !== '') {
            $where[] = '(po.document_number LIKE :s OR sup.name LIKE :s2 OR sup.trade_name LIKE :s2)';
            $w = '%' . $search . '%';
            $params['s'] = $w;
            $params['s2'] = $w;
        }
        if ($df !== null && $df !== '') {
            $where[] = 'DATE(COALESCE(po.issued_at, po.created_at)) >= :df';
            $params['df'] = $df;
        }
        if ($dt !== null && $dt !== '') {
            $where[] = 'DATE(COALESCE(po.issued_at, po.created_at)) <= :dt';
            $params['dt'] = $dt;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM purchase_orders po LEFT JOIN suppliers sup ON sup.id = po.supplier_id WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT po.*, sup.trade_name AS supplier_name
                FROM purchase_orders po
                LEFT JOIN suppliers sup ON sup.id = po.supplier_id
                WHERE {$whereSql}
                ORDER BY po.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @param list<array{product_id: int, qty: float, unit_price: float, line_discount?: float, line_total: float}> $lines
     */
    public function create(int $companyId, array $header, array $lines, ?int $userId): int
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO purchase_orders (company_id, supplier_id, document_number, status, total_amount, expected_at, notes, issued_at, store_id, supplier_quote_id, created_at, created_by)
             VALUES (:cid, :sid, :num, :st, :tot, :exp, :notes, :iss, :store, :squ, NOW(), :uid)'
        );
        $stmt->execute([
            'cid' => $companyId,
            'sid' => $header['supplier_id'],
            'num' => $header['document_number'] ?? null,
            'st' => $header['status'] ?? 'open',
            'tot' => $header['total_amount'] ?? 0,
            'exp' => $header['expected_at'] ?? null,
            'notes' => $header['notes'] ?? null,
            'iss' => $header['issued_at'] ?? null,
            'store' => $header['store_id'] ?? null,
            'squ' => $header['supplier_quote_id'] ?? null,
            'uid' => $userId,
        ]);
        $id = (int) $pdo->lastInsertId();
        if (empty($header['document_number'])) {
            $pdo->prepare('UPDATE purchase_orders SET document_number = :n WHERE id = :id')->execute([
                'n' => 'OC-' . $id,
                'id' => $id,
            ]);
        }
        $this->replaceLines($id, $lines);

        return $id;
    }

    /**
     * @param list<array{product_id: int, qty: float, unit_price: float, line_discount?: float, line_total: float}> $lines
     */
    public function updateOpen(int $id, int $companyId, array $header, array $lines, ?int $userId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE purchase_orders SET supplier_id = :sid, document_number = :num, total_amount = :tot, expected_at = :exp, notes = :notes, issued_at = :iss, store_id = :store, supplier_quote_id = :squ, updated_at = NOW()
             WHERE id = :id AND company_id = :cid AND status = \'open\' AND deleted_at IS NULL'
        );
        $stmt->execute([
            'sid' => $header['supplier_id'],
            'num' => $header['document_number'],
            'tot' => $header['total_amount'] ?? 0,
            'exp' => $header['expected_at'] ?? null,
            'notes' => $header['notes'] ?? null,
            'iss' => $header['issued_at'] ?? null,
            'store' => $header['store_id'] ?? null,
            'squ' => $header['supplier_quote_id'] ?? null,
            'id' => $id,
            'cid' => $companyId,
        ]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Compra não encontrada ou já finalizada.');
        }
        $this->replaceLines($id, $lines);
    }

    /**
     * @param list<array{product_id: int, qty: float, unit_price: float, line_discount?: float, line_total: float}> $lines
     */
    public function replaceLines(int $poId, array $lines): void
    {
        $this->pdo()->prepare('DELETE FROM purchase_order_items WHERE purchase_order_id = :id')->execute(['id' => $poId]);
        $ins = $this->pdo()->prepare(
            'INSERT INTO purchase_order_items (purchase_order_id, product_id, qty, unit_price, line_discount, line_total)
             VALUES (:pid, :prid, :qty, :up, :ld, :lt)'
        );
        foreach ($lines as $ln) {
            $ins->execute([
                'pid' => $poId,
                'prid' => $ln['product_id'],
                'qty' => $ln['qty'],
                'up' => $ln['unit_price'],
                'ld' => $ln['line_discount'] ?? 0,
                'lt' => $ln['line_total'],
            ]);
        }
    }

    /**
     * @return array{order: array<string, mixed>, lines: list<array<string, mixed>>}|null
     */
    public function findWithLines(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT po.*, sup.trade_name AS supplier_name FROM purchase_orders po
             LEFT JOIN suppliers sup ON sup.id = po.supplier_id
             WHERE po.id = :id AND po.company_id = :cid AND po.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $po = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($po === false) {
            return null;
        }
        $stmt = $this->pdo()->prepare(
            'SELECT i.*, p.name AS product_name, p.sku FROM purchase_order_items i
             LEFT JOIN products p ON p.id = i.product_id WHERE i.purchase_order_id = :id ORDER BY i.id'
        );
        $stmt->execute(['id' => $id]);

        return ['order' => $po, 'lines' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function setStatus(int $id, int $companyId, string $status): void
    {
        $this->pdo()->prepare(
            'UPDATE purchase_orders SET status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        )->execute(['st' => $status, 'id' => $id, 'cid' => $companyId]);
    }

    public function linkAccountsPayable(int $poId, int $apId): void
    {
        $this->pdo()->prepare('UPDATE purchase_orders SET accounts_payable_id = :ap WHERE id = :id')
            ->execute(['ap' => $apId, 'id' => $poId]);
    }
}
