<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PriceListRepository extends BaseRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listByCompany(int $companyId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT id, name, is_default, status FROM price_lists WHERE company_id = :cid AND deleted_at IS NULL ORDER BY is_default DESC, name ASC'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Garante ao menos uma tabela de preço e uma marcada como padrão.
     */
    public function ensureDefault(int $companyId): int
    {
        $lists = $this->listByCompany($companyId);
        foreach ($lists as $l) {
            if ((int) ($l['is_default'] ?? 0) === 1) {
                return (int) $l['id'];
            }
        }
        if ($lists !== []) {
            $firstId = (int) $lists[0]['id'];
            $this->setDefault($firstId, $companyId);

            return $firstId;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO price_lists (company_id, name, is_default, status, created_at, updated_at) VALUES (:cid, :name, 1, 1, NOW(), NOW())'
        );
        $stmt->execute(['cid' => $companyId, 'name' => 'Tabela padrão']);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByIdForCompany(int $id, int $companyId): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM price_lists WHERE id = :id AND company_id = :cid AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'cid' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }

    public function setDefault(int $listId, int $companyId): void
    {
        $this->pdo()->prepare('UPDATE price_lists SET is_default = 0, updated_at = NOW() WHERE company_id = :cid')->execute(['cid' => $companyId]);
        $this->pdo()->prepare('UPDATE price_lists SET is_default = 1, updated_at = NOW() WHERE id = :id AND company_id = :cid')->execute(['id' => $listId, 'cid' => $companyId]);
    }

    public function createList(int $companyId, string $name): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO price_lists (company_id, name, is_default, status, created_at, updated_at) VALUES (:cid, :name, 0, 1, NOW(), NOW())'
        );
        $stmt->execute(['cid' => $companyId, 'name' => $name]);

        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function productsWithPrices(int $companyId, int $listId): array
    {
        $sql = 'SELECT p.id, p.sku, p.name, p.sale_price AS base_price,
                ppli.price AS list_price
                FROM products p
                LEFT JOIN product_price_list_items ppli ON ppli.product_id = p.id AND ppli.price_list_id = :lid
                WHERE p.company_id = :cid AND p.deleted_at IS NULL
                ORDER BY p.name ASC';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['lid' => $listId, 'cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function upsertItem(int $listId, int $productId, string $price): void
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO product_price_list_items (price_list_id, product_id, price, created_at, updated_at)
             VALUES (:lid, :pid, :price, NOW(), NOW())
             ON DUPLICATE KEY UPDATE price = VALUES(price), updated_at = NOW()'
        );
        $stmt->execute(['lid' => $listId, 'pid' => $productId, 'price' => $price]);
    }
}
