<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class FinancialReportRepository extends BaseRepository
{
    /**
     * Receita aproximada: vendas no período (documentos emitidos).
     */
    public function salesTotal(int $companyId, string $start, string $end): float
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COALESCE(SUM(total_amount),0) FROM sales_documents
             WHERE company_id = :cid AND deleted_at IS NULL
             AND issued_at >= :s AND issued_at < DATE_ADD(:e, INTERVAL 1 DAY)'
        );
        $stmt->execute(['cid' => $companyId, 's' => $start . ' 00:00:00', 'e' => $end]);

        return (float) $stmt->fetchColumn();
    }

    /**
     * @return list<array{ym: string, entradas: float, saidas: float}>
     */
    public function cashFlowMonthly(int $companyId, int $months): array
    {
        $months = max(1, min(24, $months));
        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $d = new \DateTimeImmutable('first day of this month');
            $d = $d->modify("-{$i} months");
            $start = $d->format('Y-m-01');
            $end = $d->format('Y-m-t');
            $ym = $d->format('Y-m');

            $stmt = $this->pdo()->prepare(
                'SELECT COALESCE(SUM(paid_amount),0) FROM accounts_receivable WHERE company_id = :cid AND deleted_at IS NULL
                 AND status = \'paid\' AND updated_at >= :s AND updated_at < DATE_ADD(:e2, INTERVAL 1 DAY)'
            );
            $stmt->execute(['cid' => $companyId, 's' => $start . ' 00:00:00', 'e2' => $end . ' 23:59:59']);
            $in = (float) $stmt->fetchColumn();

            $stmt = $this->pdo()->prepare(
                'SELECT COALESCE(SUM(paid_amount),0) FROM accounts_payable WHERE company_id = :cid AND deleted_at IS NULL
                 AND status = \'paid\' AND updated_at >= :s AND updated_at < DATE_ADD(:e2, INTERVAL 1 DAY)'
            );
            $stmt->execute(['cid' => $companyId, 's' => $start . ' 00:00:00', 'e2' => $end . ' 23:59:59']);
            $outF = (float) $stmt->fetchColumn();

            $out[] = ['ym' => $ym, 'entradas' => $in, 'saidas' => $outF];
        }

        return $out;
    }

    /**
     * DRE simplificado: receita (vendas no período) vs despesas (títulos a pagar com vencimento no período).
     *
     * @return array{receita: float, despesas: float, resultado: float}
     */
    public function dreSummary(int $companyId, string $start, string $end): array
    {
        $receita = $this->salesTotal($companyId, $start, $end);
        $stmt = $this->pdo()->prepare(
            'SELECT COALESCE(SUM(amount),0) FROM accounts_payable WHERE company_id = :cid AND deleted_at IS NULL
             AND status != \'cancelled\' AND due_date BETWEEN :s AND :e'
        );
        $stmt->execute(['cid' => $companyId, 's' => $start, 'e' => $end]);
        $despesas = (float) $stmt->fetchColumn();

        return [
            'receita' => $receita,
            'despesas' => $despesas,
            'resultado' => $receita - $despesas,
        ];
    }
}
