<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class DashboardStatsRepository extends BaseRepository
{
    /**
     * @return array<string, mixed>
     */
    public function aggregateForCompany(int $companyId): array
    {
        $pdo = $this->pdo();

        $count = static function (PDO $pdo, string $sql, array $params): int {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        };

        $clients = $count(
            $pdo,
            'SELECT COUNT(*) FROM clients WHERE company_id = :c AND deleted_at IS NULL',
            ['c' => $companyId]
        );
        $suppliers = $count(
            $pdo,
            'SELECT COUNT(*) FROM suppliers WHERE company_id = :c AND deleted_at IS NULL',
            ['c' => $companyId]
        );
        $products = $count(
            $pdo,
            'SELECT COUNT(*) FROM products WHERE company_id = :c AND deleted_at IS NULL',
            ['c' => $companyId]
        );
        $services = $count(
            $pdo,
            'SELECT COUNT(*) FROM services WHERE company_id = :c AND deleted_at IS NULL',
            ['c' => $companyId]
        );

        $employees = $this->tableExists('employees')
            ? $count($pdo, 'SELECT COUNT(*) FROM employees WHERE company_id = :c AND deleted_at IS NULL', ['c' => $companyId])
            : 0;
        $carriers = $this->tableExists('carriers')
            ? $count($pdo, 'SELECT COUNT(*) FROM carriers WHERE company_id = :c AND deleted_at IS NULL', ['c' => $companyId])
            : 0;

        $lowStock = $count(
            $pdo,
            'SELECT COUNT(*) FROM products WHERE company_id = :c AND deleted_at IS NULL AND status = 1 AND stock_qty < stock_min',
            ['c' => $companyId]
        );

        $arOpen = 0.0;
        $apOpen = 0.0;
        if ($this->tableExists('accounts_receivable')) {
            $stmt = $pdo->prepare(
                "SELECT COALESCE(SUM(amount - paid_amount), 0) FROM accounts_receivable
                 WHERE company_id = :c AND deleted_at IS NULL AND status = 'open'"
            );
            $stmt->execute(['c' => $companyId]);
            $arOpen = (float) $stmt->fetchColumn();
        }
        if ($this->tableExists('accounts_payable')) {
            $stmt = $pdo->prepare(
                "SELECT COALESCE(SUM(amount - paid_amount), 0) FROM accounts_payable
                 WHERE company_id = :c AND deleted_at IS NULL AND status = 'open'"
            );
            $stmt->execute(['c' => $companyId]);
            $apOpen = (float) $stmt->fetchColumn();
        }

        $salesMonth = 0.0;
        if ($this->tableExists('sales_documents')) {
            $stmt = $pdo->prepare(
                'SELECT COALESCE(SUM(total_amount), 0) FROM sales_documents
                 WHERE company_id = :c AND deleted_at IS NULL
                 AND YEAR(issued_at) = YEAR(CURDATE()) AND MONTH(issued_at) = MONTH(CURDATE())'
            );
            $stmt->execute(['c' => $companyId]);
            $salesMonth = (float) $stmt->fetchColumn();
        }

        $osOpen = 0;
        if ($this->tableExists('service_orders')) {
            $osOpen = $count(
                $pdo,
                "SELECT COUNT(*) FROM service_orders WHERE company_id = :c AND deleted_at IS NULL
                 AND status IN ('open','in_progress')",
                ['c' => $companyId]
            );
        }

        $budgetsPending = 0;

        return [
            'clients' => $clients,
            'suppliers' => $suppliers,
            'products' => $products,
            'services' => $services,
            'employees' => $employees,
            'carriers' => $carriers,
            'low_stock' => $lowStock,
            'ar_open' => $arOpen,
            'ap_open' => $apOpen,
            'sales_month' => $salesMonth,
            'os_open' => $osOpen,
            'budgets_pending' => $budgetsPending,
        ];
    }

    /**
     * @return list<float>
     */
    public function salesLast12Months(int $companyId): array
    {
        if (!$this->tableExists('sales_documents')) {
            return array_fill(0, 12, 0.0);
        }
        $stmt = $this->pdo()->prepare(
            "SELECT DATE_FORMAT(issued_at, '%Y-%m') AS ym, COALESCE(SUM(total_amount), 0) AS total
             FROM sales_documents
             WHERE company_id = :c AND deleted_at IS NULL
               AND issued_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')
             GROUP BY ym ORDER BY ym ASC"
        );
        $stmt->execute(['c' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $out = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = new \DateTimeImmutable('first day of this month');
            $d = $d->modify("-{$i} months");
            $key = $d->format('Y-m');
            $out[] = isset($rows[$key]) ? (float) $rows[$key] : 0.0;
        }

        return $out;
    }

    /**
     * Fluxo simplificado: soma de vendas mensais (mesma série) e segunda série = metade despesas estimada por AR/AP não usado aqui.
     * @return list<float>
     */
    public function cashflowProxyLast12Months(int $companyId): array
    {
        $sales = $this->salesLast12Months($companyId);
        $out = [];
        foreach ($sales as $v) {
            $out[] = round($v * 0.35, 2);
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentAuditLogs(int $limit): array
    {
        if (!$this->tableExists('audit_logs')) {
            return [];
        }
        $stmt = $this->pdo()->prepare(
            'SELECT id, action, module, description, created_at FROM audit_logs
             ORDER BY created_at DESC LIMIT ' . (int) max(1, $limit)
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentSupportTickets(int $companyId, int $limit): array
    {
        if (!$this->tableExists('support_tickets')) {
            return [];
        }
        $stmt = $this->pdo()->prepare(
            'SELECT id, subject, status, priority, created_at FROM support_tickets
             WHERE company_id = :c AND deleted_at IS NULL
             ORDER BY created_at DESC LIMIT ' . (int) max(1, $limit)
        );
        $stmt->execute(['c' => $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function tableExists(string $table): bool
    {
        // SHOW TABLES falha com PDO nativo (1295). information_schema com :t pode dar 1064 em alguns MySQL/MariaDB.
        // Nome da tabela vem só de constantes internas — validar e usar quote() (sem placeholder).
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return false;
        }
        $pdo = $this->pdo();
        $sql = 'SELECT COUNT(*) FROM information_schema.tables
                WHERE table_schema = DATABASE() AND table_name = ' . $pdo->quote($table);
        $stmt = $pdo->query($sql);

        return $stmt !== false && (int) $stmt->fetchColumn() > 0;
    }
}
