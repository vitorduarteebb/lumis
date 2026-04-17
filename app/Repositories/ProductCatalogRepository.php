<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Categorias, marcas e unidades do catálogo (CRUD administrativo).
 */
final class ProductCatalogRepository extends BaseRepository
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function categoriesPaginate(int $companyId, string $q, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = 'company_id = :cid';
        $params = ['cid' => $companyId];
        if ($q !== '') {
            $where .= ' AND (name LIKE :q OR slug LIKE :q2)';
            $w = '%' . $q . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
        }
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM product_categories WHERE {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT * FROM product_categories WHERE {$where} ORDER BY name ASC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function brandsPaginate(int $companyId, string $q, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = 'company_id = :cid';
        $params = ['cid' => $companyId];
        if ($q !== '') {
            $where .= ' AND name LIKE :q';
            $params['q'] = '%' . $q . '%';
        }
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM product_brands WHERE {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT * FROM product_brands WHERE {$where} ORDER BY name ASC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function unitsPaginate(int $companyId, string $q, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = 'company_id = :cid';
        $params = ['cid' => $companyId];
        if ($q !== '') {
            $where .= ' AND (name LIKE :q OR abbreviation LIKE :q2)';
            $w = '%' . $q . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
        }
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM product_units WHERE {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT * FROM product_units WHERE {$where} ORDER BY name ASC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findCategory(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM product_categories WHERE id = :id AND company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findBrand(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM product_brands WHERE id = :id AND company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findUnit(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM product_units WHERE id = :id AND company_id = :cid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function categorySlugExists(int $companyId, string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM product_categories WHERE company_id = :cid AND slug = :slug';
        $params = ['cid' => $companyId, 'slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id != :eid';
            $params['eid'] = $exceptId;
        }
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function insertCategory(int $companyId, string $name, string $slug, int $status): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO product_categories (company_id, name, slug, status, created_at, updated_at) VALUES (:cid, :name, :slug, :st, NOW(), NOW())'
        );
        $stmt->execute(['cid' => $companyId, 'name' => $name, 'slug' => $slug, 'st' => $status]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateCategory(int $id, int $companyId, string $name, string $slug, int $status): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE product_categories SET name = :name, slug = :slug, status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['name' => $name, 'slug' => $slug, 'st' => $status, 'id' => $id, 'cid' => $companyId]);
    }

    public function insertBrand(int $companyId, string $name, int $status): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO product_brands (company_id, name, status, created_at, updated_at) VALUES (:cid, :name, :st, NOW(), NOW())'
        );
        $stmt->execute(['cid' => $companyId, 'name' => $name, 'st' => $status]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateBrand(int $id, int $companyId, string $name, int $status): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE product_brands SET name = :name, status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['name' => $name, 'st' => $status, 'id' => $id, 'cid' => $companyId]);
    }

    public function insertUnit(int $companyId, string $name, string $abbr, int $status): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO product_units (company_id, name, abbreviation, status, created_at, updated_at) VALUES (:cid, :name, :abbr, :st, NOW(), NOW())'
        );
        $stmt->execute(['cid' => $companyId, 'name' => $name, 'abbr' => $abbr, 'st' => $status]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateUnit(int $id, int $companyId, string $name, string $abbr, int $status): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE product_units SET name = :name, abbreviation = :abbr, status = :st, updated_at = NOW() WHERE id = :id AND company_id = :cid'
        );
        $stmt->execute(['name' => $name, 'abbr' => $abbr, 'st' => $status, 'id' => $id, 'cid' => $companyId]);
    }

    public function countProductsUsingCategory(int $categoryId, int $companyId): int
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM products WHERE company_id = :cid AND category_id = :cat AND deleted_at IS NULL'
        );
        $stmt->execute(['cid' => $companyId, 'cat' => $categoryId]);

        return (int) $stmt->fetchColumn();
    }

    public function countProductsUsingBrand(int $brandId, int $companyId): int
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM products WHERE company_id = :cid AND brand_id = :bid AND deleted_at IS NULL'
        );
        $stmt->execute(['cid' => $companyId, 'bid' => $brandId]);

        return (int) $stmt->fetchColumn();
    }

    public function countProductsUsingUnit(int $unitId, int $companyId): int
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM products WHERE company_id = :cid AND unit_id = :uid AND deleted_at IS NULL'
        );
        $stmt->execute(['cid' => $companyId, 'uid' => $unitId]);

        return (int) $stmt->fetchColumn();
    }
}
