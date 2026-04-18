<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class StockReturnRepository extends BaseRepository
{
    public function paginate(int $companyId, string $search, ?string $kind, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['r.company_id = :cid', 'r.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($kind !== null && $kind !== '' && $kind !== 'all') {
            $where[] = 'r.return_kind = :k';
            $params['k'] = $kind;
        }
        if ($search !== '') {
            $where[] = '(r.reason LIKE :q OR r.notes LIKE :q2 OR p.name LIKE :q3)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM stock_returns r LEFT JOIN products p ON p.id = r.product_id WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT r.*, p.name AS product_name, c.name AS client_name, s.trade_name AS supplier_name
                FROM stock_returns r
                LEFT JOIN products p ON p.id = r.product_id
                LEFT JOIN clients c ON c.id = r.client_id
                LEFT JOIN suppliers s ON s.id = r.supplier_id
                WHERE {$whereSql}
                ORDER BY r.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    public function insert(int $companyId, array $data): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO stock_returns (company_id, store_id, return_kind, client_id, supplier_id, sales_document_id, purchase_order_id, product_id, qty, reason, notes, status, created_by, created_at)
             VALUES (:cid, :sid, :rk, :cl, :sup, :sd, :po, :pid, :qty, :reas, :notes, :st, :uid, NOW())'
        );
        $stmt->execute([
            'cid' => $companyId,
            'sid' => $data['store_id'] ?? null,
            'rk' => $data['return_kind'],
            'cl' => $data['client_id'] ?? null,
            'sup' => $data['supplier_id'] ?? null,
            'sd' => $data['sales_document_id'] ?? null,
            'po' => $data['purchase_order_id'] ?? null,
            'pid' => $data['product_id'],
            'qty' => $data['qty'],
            'reas' => $data['reason'] ?? null,
            'notes' => $data['notes'] ?? null,
            'st' => $data['status'] ?? 'recorded',
            'uid' => $data['created_by'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT r.*, p.name AS product_name FROM stock_returns r
             LEFT JOIN products p ON p.id = r.product_id
             WHERE r.id = :id AND r.company_id = :cid AND r.deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }
}
