<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

/**
 * Numeração por empresa, loja (0=todas), modelo, ambiente e série.
 */
final class FiscalSeriesRepository extends BaseRepository
{
    /**
     * Próximo número disponível (incrementa e devolve o novo valor) — transação.
     *
     * @throws \RuntimeException
     */
    public function reserveNextNumber(
        int $companyId,
        int $storeId,
        int $fiscalModel,
        int $tpAmb,
        string $series
    ): int {
        if (!$this->tableExists('fiscal_series')) {
            throw new \RuntimeException('Tabela fiscal_series inexistente. Execute as migrations.');
        }
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            $sel = $pdo->prepare(
                'SELECT id, last_number FROM fiscal_series
                 WHERE company_id = :cid AND store_id = :stid AND fiscal_model = :fm AND tp_amb = :amb AND series = :ser
                 FOR UPDATE'
            );
            $sel->execute([
                'cid' => $companyId,
                'stid' => $storeId,
                'fm' => $fiscalModel,
                'amb' => $tpAmb,
                'ser' => $series,
            ]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                $ins = $pdo->prepare(
                    'INSERT INTO fiscal_series (company_id, store_id, fiscal_model, tp_amb, series, last_number, updated_at)
                     VALUES (:cid, :stid, :fm, :amb, :ser, 1, NOW())'
                );
                $ins->execute([
                    'cid' => $companyId,
                    'stid' => $storeId,
                    'fm' => $fiscalModel,
                    'amb' => $tpAmb,
                    'ser' => $series,
                ]);
                $next = 1;
            } else {
                $id = (int) $row['id'];
                $next = (int) $row['last_number'] + 1;
                $upd = $pdo->prepare('UPDATE fiscal_series SET last_number = :n, updated_at = NOW() WHERE id = :id');
                $upd->execute(['n' => $next, 'id' => $id]);
            }
            $pdo->commit();

            return $next;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \RuntimeException('Falha ao reservar numeração fiscal: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listByCompany(int $companyId): array
    {
        if (!$this->tableExists('fiscal_series')) {
            return [];
        }
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM fiscal_series WHERE company_id = :cid ORDER BY fiscal_model, tp_amb, series, store_id'
        );
        $stmt->execute(['cid' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
