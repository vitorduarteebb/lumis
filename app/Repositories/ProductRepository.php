<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function categoriesForCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name FROM product_categories WHERE company_id = :cid AND status = 1 ORDER BY name'
        );
        $stmt->execute(['cid' => $companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function brandsForCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name FROM product_brands WHERE company_id = :cid AND status = 1 ORDER BY name'
        );
        $stmt->execute(['cid' => $companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function unitsForCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name, abbreviation FROM product_units WHERE company_id = :cid AND status = 1 ORDER BY name'
        );
        $stmt->execute(['cid' => $companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $sql = 'SELECT p.*, pc.name AS category_name, pb.name AS brand_name, pu.name AS unit_name, pu.abbreviation AS unit_abbr
                FROM products p
                LEFT JOIN product_categories pc ON pc.id = p.category_id
                LEFT JOIN product_brands pb ON pb.id = p.brand_id
                LEFT JOIN product_units pu ON pu.id = p.unit_id
                WHERE p.id = :id AND p.company_id = :cid AND p.deleted_at IS NULL LIMIT 1';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function paginate(int $companyId, string $search, ?string $statusFilter, ?int $categoryId, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = ['p.company_id = :cid', 'p.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($search !== '') {
            $where[] = '(p.name LIKE :q OR p.sku LIKE :q2 OR p.internal_code LIKE :q3 OR p.barcode LIKE :q4)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
            $params['q4'] = $w;
        }
        if ($statusFilter === '1' || $statusFilter === '0') {
            $where[] = 'p.status = :st';
            $params['st'] = (int) $statusFilter;
        }
        if ($categoryId !== null && $categoryId > 0) {
            $where[] = 'p.category_id = :cat';
            $params['cat'] = $categoryId;
        }
        $whereSql = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) FROM products p WHERE {$whereSql}";
        $stmt = $this->pdo()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT p.*, pc.name AS category_name, pb.name AS brand_name
                FROM products p
                LEFT JOIN product_categories pc ON pc.id = p.category_id
                LEFT JOIN product_brands pb ON pb.id = p.brand_id
                WHERE {$whereSql}
                ORDER BY p.name ASC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['rows' => $rows, 'total' => $total];
    }

    public function skuExists(int $companyId, string $sku, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE company_id = :cid AND sku = :sku AND deleted_at IS NULL';
        $params = ['cid' => $companyId, 'sku' => $sku];
        if ($exceptId !== null) {
            $sql .= ' AND id != :eid';
            $params['eid'] = $exceptId;
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(int $companyId, array $data, ?int $createdBy): int
    {
        $sql = 'INSERT INTO products (
            company_id, category_id, brand_id, unit_id, name, sku, internal_code, barcode,
            cost, sale_price, stock_qty, stock_min, description, status, created_by, created_at, updated_at
        ) VALUES (
            :company_id, :category_id, :brand_id, :unit_id, :name, :sku, :internal_code, :barcode,
            :cost, :sale_price, :stock_qty, :stock_min, :description, :status, :created_by, NOW(), NOW()
        )';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'company_id' => $companyId,
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'],
            'unit_id' => $data['unit_id'],
            'name' => $data['name'],
            'sku' => $data['sku'],
            'internal_code' => $data['internal_code'],
            'barcode' => $data['barcode'],
            'cost' => $data['cost'],
            'sale_price' => $data['sale_price'],
            'stock_qty' => $data['stock_qty'],
            'stock_min' => $data['stock_min'],
            'description' => $data['description'],
            'status' => $data['status'],
            'created_by' => $createdBy,
        ]);
        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, int $companyId, array $data, ?int $updatedBy): void
    {
        $sql = 'UPDATE products SET
            category_id = :category_id, brand_id = :brand_id, unit_id = :unit_id, name = :name, sku = :sku,
            internal_code = :internal_code, barcode = :barcode, cost = :cost, sale_price = :sale_price,
            stock_qty = :stock_qty, stock_min = :stock_min, description = :description, status = :status,
            updated_by = :updated_by, updated_at = NOW()
            WHERE id = :id AND company_id = :cid AND deleted_at IS NULL';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'cid' => $companyId,
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'],
            'unit_id' => $data['unit_id'],
            'name' => $data['name'],
            'sku' => $data['sku'],
            'internal_code' => $data['internal_code'],
            'barcode' => $data['barcode'],
            'cost' => $data['cost'],
            'sale_price' => $data['sale_price'],
            'stock_qty' => $data['stock_qty'],
            'stock_min' => $data['stock_min'],
            'description' => $data['description'],
            'status' => $data['status'],
            'updated_by' => $updatedBy,
        ]);
    }

    public function softDelete(int $id, int $companyId): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE products SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id AND company_id = :cid AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
    }

    /**
     * @param list<int> $ids
     * @return list<array<string, mixed>>
     */
    public function findByIdsForCompany(array $ids, int $companyId): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $v): bool => $v > 0));
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT p.*, pc.name AS category_name FROM products p
                LEFT JOIN product_categories pc ON pc.id = p.category_id
                WHERE p.company_id = ? AND p.deleted_at IS NULL AND p.id IN ({$placeholders})
                ORDER BY p.name ASC";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(array_merge([$companyId], $ids));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista enxuta para selects em orçamentos / vendas.
     *
     * @return list<array<string, mixed>>
     */
    public function listForSelect(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name, sku, sale_price FROM products WHERE company_id = :cid AND deleted_at IS NULL AND status = 1 ORDER BY name ASC LIMIT 500'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Baixa estoque na venda a partir de orçamento. Retorna false se saldo insuficiente.
     * Abre transação própria (uso pontual).
     */
    public function tryDecrementStockForSale(int $companyId, int $productId, float $qty, string $reference, ?int $userId): bool
    {
        if ($qty <= 0) {
            return true;
        }
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            $ok = $this->applyStockOutNoTx($companyId, $productId, $qty, $reference, $userId);
            if (!$ok) {
                $pdo->rollBack();

                return false;
            }
            $pdo->commit();

            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Baixa estoque dentro de uma transação já aberta (ex.: venda + orçamento no mesmo commit).
     */
    public function applyStockOutNoTx(int $companyId, int $productId, float $qty, string $reference, ?int $userId): bool
    {
        if ($qty <= 0) {
            return true;
        }
        $pdo = $this->pdo();
        $stmt = $pdo->prepare(
            'SELECT stock_qty FROM products WHERE id = :pid AND company_id = :cid AND deleted_at IS NULL FOR UPDATE'
        );
        $stmt->execute(['pid' => $productId, 'cid' => $companyId]);
        $cur = (float) $stmt->fetchColumn();
        if ($cur + 1e-9 < $qty) {
            return false;
        }
        $new = $cur - $qty;
        $pdo->prepare(
            'UPDATE products SET stock_qty = :nq, updated_at = NOW() WHERE id = :pid AND company_id = :cid'
        )->execute(['nq' => $new, 'pid' => $productId, 'cid' => $companyId]);
        $pdo->prepare(
            'INSERT INTO stock_movements (company_id, product_id, movement_type, qty, reference, notes, created_by, created_at)
             VALUES (:cid, :pid, :mt, :qty, :ref, :notes, :uid, NOW())'
        )->execute([
            'cid' => $companyId,
            'pid' => $productId,
            'mt' => 'out',
            'qty' => $qty,
            'ref' => $reference,
            'notes' => 'Saída por venda (orçamento convertido)',
            'uid' => $userId,
        ]);

        return true;
    }
}
