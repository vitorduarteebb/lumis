<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Consultas agregadas para o módulo Relatórios (somente leitura).
 */
final class ReportsRepository extends BaseRepository
{
    /**
     * Cadastros: totais por entidade.
     *
     * @param array{status?: string, date_from?: string|null, date_to?: string|null} $filters
     * @return array<string, int|float>
     */
    public function cadastrosTotals(int $companyId, array $filters): array
    {
        $st = (string) ($filters['status'] ?? 'all');
        $stSql = '';
        $params = ['cid' => $companyId];
        if ($st === 'active') {
            $stSql = ' AND status = 1';
        } elseif ($st === 'inactive') {
            $stSql = ' AND status = 0';
        }
        $dateClause = '';
        $df = trim((string) ($filters['date_from'] ?? ''));
        $dt = trim((string) ($filters['date_to'] ?? ''));
        if ($df !== '') {
            $dateClause .= ' AND DATE(created_at) >= :df';
            $params['df'] = $df;
        }
        if ($dt !== '') {
            $dateClause .= ' AND DATE(created_at) <= :dt';
            $params['dt'] = $dt;
        }

        $out = ['clients' => 0, 'suppliers' => 0, 'employees' => 0, 'carriers' => 0, 'clients_active' => 0, 'clients_inactive' => 0];
        foreach (['clients', 'suppliers', 'employees', 'carriers'] as $table) {
            $del = $this->tableHasDeletedAt($table) ? ' AND deleted_at IS NULL' : '';
            $sql = "SELECT COUNT(*) FROM {$table} WHERE company_id = :cid {$del} {$stSql} {$dateClause}";
            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);
            $out[$table] = (int) $stmt->fetchColumn();
        }
        if ($st === 'all' && $df === '' && $dt === '') {
            $stmt = $this->pdo()->prepare('SELECT SUM(status = 1), SUM(status = 0) FROM clients WHERE company_id = :cid AND deleted_at IS NULL');
            $stmt->execute(['cid' => $companyId]);
            $r = $stmt->fetch(PDO::FETCH_NUM);
            if (is_array($r)) {
                $out['clients_active'] = (int) ($r[0] ?? 0);
                $out['clients_inactive'] = (int) ($r[1] ?? 0);
            }
        }

        return $out;
    }

    private function tableHasDeletedAt(string $table): bool
    {
        return $this->tableExists($table) && $this->columnExists($table, 'deleted_at');
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function cadastrosRows(
        int $companyId,
        string $entity,
        string $search,
        string $status,
        ?string $dateFrom,
        ?string $dateTo,
        int $page,
        int $perPage
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $map = [
            'clients' => 'clients',
            'suppliers' => 'suppliers',
            'employees' => 'employees',
            'carriers' => 'carriers',
        ];
        $table = $map[$entity] ?? 'clients';
        $nameCol = $table === 'suppliers' ? 'COALESCE(trade_name, legal_name)' : 'name';
        $where = ['company_id = :cid'];
        $params = ['cid' => $companyId];
        if ($this->tableHasDeletedAt($table)) {
            $where[] = 'deleted_at IS NULL';
        }
        if ($status === 'active') {
            $where[] = 'status = 1';
        } elseif ($status === 'inactive') {
            $where[] = 'status = 0';
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $where[] = 'DATE(created_at) >= :df';
            $params['df'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $where[] = 'DATE(created_at) <= :dt';
            $params['dt'] = $dateTo;
        }
        if ($search !== '') {
            $where[] = "({$nameCol} LIKE :q OR email LIKE :q2 OR document LIKE :q3)";
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
            $params['q3'] = $w;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT *, {$nameCol} AS display_name FROM {$table} WHERE {$whereSql} ORDER BY id DESC LIMIT "
            . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @param array{date_from?: string|null, date_to?: string|null, client_id?: int, store_id?: int, seller_user_id?: int, status?: string} $f
     * @return array{count:int, total_amount:float, avg_ticket:float}
     */
    public function vendasSummary(int $companyId, string $vendaKind, array $f): array
    {
        $where = ['sd.company_id = :cid', 'sd.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($vendaKind === 'product') {
            $where[] = "sd.document_kind IN ('product', 'balcao')";
        } elseif ($vendaKind === 'balcao') {
            $where[] = "sd.document_kind = 'balcao'";
        } elseif ($vendaKind === 'service') {
            $where[] = "sd.document_kind = 'service'";
        } elseif ($vendaKind === 'all') {
            // sem filtro de tipo
        } else {
            $where[] = '1=1';
        }
        $st = (string) ($f['status'] ?? 'all');
        if ($st !== 'all' && $st !== '') {
            $where[] = 'sd.status = :st';
            $params['st'] = $st;
        }
        if (!empty($f['client_id'])) {
            $where[] = 'sd.client_id = :cl';
            $params['cl'] = (int) $f['client_id'];
        }
        if (!empty($f['store_id'])) {
            $where[] = 'sd.store_id = :sid';
            $params['sid'] = (int) $f['store_id'];
        }
        if (!empty($f['seller_user_id'])) {
            $where[] = 'sd.seller_user_id = :sl';
            $params['sl'] = (int) $f['seller_user_id'];
        }
        $df = trim((string) ($f['date_from'] ?? ''));
        $dt = trim((string) ($f['date_to'] ?? ''));
        if ($df !== '') {
            $where[] = 'DATE(COALESCE(sd.issued_at, sd.created_at)) >= :df';
            $params['df'] = $df;
        }
        if ($dt !== '') {
            $where[] = 'DATE(COALESCE(sd.issued_at, sd.created_at)) <= :dt';
            $params['dt'] = $dt;
        }
        $whereSql = implode(' AND ', $where);
        $sql = "SELECT COUNT(*), COALESCE(SUM(sd.total_amount),0), COALESCE(AVG(sd.total_amount),0)
                FROM sales_documents sd WHERE {$whereSql}";
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_NUM);

        return [
            'count' => (int) ($r[0] ?? 0),
            'total_amount' => (float) ($r[1] ?? 0),
            'avg_ticket' => (float) ($r[2] ?? 0),
        ];
    }

    /**
     * @param array{date_from?: string|null, date_to?: string|null, client_id?: int, store_id?: int, seller_user_id?: int, status?: string, q?: string} $f
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function vendasRows(int $companyId, string $vendaKind, array $f, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['sd.company_id = :cid', 'sd.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($vendaKind === 'product') {
            $where[] = "sd.document_kind IN ('product', 'balcao')";
        } elseif ($vendaKind === 'balcao') {
            $where[] = "sd.document_kind = 'balcao'";
        } elseif ($vendaKind === 'service') {
            $where[] = "sd.document_kind = 'service'";
        } elseif ($vendaKind !== 'all') {
            $where[] = "sd.document_kind IN ('product', 'balcao', 'service')";
        }
        $st = (string) ($f['status'] ?? 'all');
        if ($st !== 'all' && $st !== '') {
            $where[] = 'sd.status = :st';
            $params['st'] = $st;
        }
        if (!empty($f['client_id'])) {
            $where[] = 'sd.client_id = :cl';
            $params['cl'] = (int) $f['client_id'];
        }
        if (!empty($f['store_id'])) {
            $where[] = 'sd.store_id = :sid';
            $params['sid'] = (int) $f['store_id'];
        }
        if (!empty($f['seller_user_id'])) {
            $where[] = 'sd.seller_user_id = :sl';
            $params['sl'] = (int) $f['seller_user_id'];
        }
        $df = trim((string) ($f['date_from'] ?? ''));
        $dt = trim((string) ($f['date_to'] ?? ''));
        if ($df !== '') {
            $where[] = 'DATE(COALESCE(sd.issued_at, sd.created_at)) >= :df';
            $params['df'] = $df;
        }
        if ($dt !== '') {
            $where[] = 'DATE(COALESCE(sd.issued_at, sd.created_at)) <= :dt';
            $params['dt'] = $dt;
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(sd.document_number LIKE :sq OR c.name LIKE :sq2)';
            $w = '%' . $q . '%';
            $params['sq'] = $w;
            $params['sq2'] = $w;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM sales_documents sd LEFT JOIN clients c ON c.id = sd.client_id WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT sd.*, c.name AS client_name, u.name AS seller_name, st.name AS store_name
                FROM sales_documents sd
                LEFT JOIN clients c ON c.id = sd.client_id
                LEFT JOIN users u ON u.id = sd.seller_user_id
                LEFT JOIN stores st ON st.id = sd.store_id
                WHERE {$whereSql}
                ORDER BY sd.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @param array{status?: string, assigned_user_id?: int, client_id?: string|int, date_from?: string|null, date_to?: string|null, q?: string} $f
     * @return array{total:int, open:int, progress:int, done:int, cancelled:int, avg_hours:float|null}
     */
    public function ordensServicoSummary(int $companyId, array $f): array
    {
        $where = ['so.company_id = :cid'];
        $params = ['cid' => $companyId];
        if ($this->columnExists('service_orders', 'deleted_at')) {
            $where[] = 'so.deleted_at IS NULL';
        }
        $st = (string) ($f['status'] ?? 'all');
        if ($st !== 'all' && $st !== '') {
            $where[] = 'so.status = :st';
            $params['st'] = $st;
        }
        if (!empty($f['assigned_user_id'])) {
            $where[] = 'so.assigned_user_id = :au';
            $params['au'] = (int) $f['assigned_user_id'];
        }
        if (!empty($f['client_id'])) {
            $where[] = 'so.client_id = :cl';
            $params['cl'] = (int) $f['client_id'];
        }
        $df = trim((string) ($f['date_from'] ?? ''));
        $dt = trim((string) ($f['date_to'] ?? ''));
        if ($df !== '') {
            $where[] = 'DATE(so.opened_at) >= :df';
            $params['df'] = $df;
        }
        if ($dt !== '') {
            $where[] = 'DATE(so.opened_at) <= :dt';
            $params['dt'] = $dt;
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(so.code LIKE :sq OR so.description LIKE :sq2 OR c.name LIKE :sq3)';
            $w = '%' . $q . '%';
            $params['sq'] = $w;
            $params['sq2'] = $w;
            $params['sq3'] = $w;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*),
                SUM(so.status = 'open'),
                SUM(so.status = 'in_progress'),
                SUM(so.status = 'done'),
                SUM(so.status = 'cancelled')
             FROM service_orders so
             LEFT JOIN clients c ON c.id = so.client_id
             WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_NUM);
        $avg = null;
        if ($this->columnExists('service_orders', 'completed_at')) {
            $stmt = $this->pdo()->prepare(
                "SELECT AVG(TIMESTAMPDIFF(HOUR, so.opened_at, so.completed_at))
                 FROM service_orders so
                 LEFT JOIN clients c ON c.id = so.client_id
                 WHERE {$whereSql} AND so.completed_at IS NOT NULL"
            );
            $stmt->execute($params);
            $avg = $stmt->fetchColumn();
            $avg = $avg !== null && $avg !== false ? (float) $avg : null;
        }

        return [
            'total' => (int) ($r[0] ?? 0),
            'open' => (int) ($r[1] ?? 0),
            'progress' => (int) ($r[2] ?? 0),
            'done' => (int) ($r[3] ?? 0),
            'cancelled' => (int) ($r[4] ?? 0),
            'avg_hours' => $avg,
        ];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function ordensServicoRows(int $companyId, array $f, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['so.company_id = :cid'];
        $params = ['cid' => $companyId];
        if ($this->columnExists('service_orders', 'deleted_at')) {
            $where[] = 'so.deleted_at IS NULL';
        }
        $st = (string) ($f['status'] ?? 'all');
        if ($st !== 'all' && $st !== '') {
            $where[] = 'so.status = :st';
            $params['st'] = $st;
        }
        if (!empty($f['assigned_user_id'])) {
            $where[] = 'so.assigned_user_id = :au';
            $params['au'] = (int) $f['assigned_user_id'];
        }
        if (!empty($f['client_id'])) {
            $where[] = 'so.client_id = :cl';
            $params['cl'] = (int) $f['client_id'];
        }
        $df = trim((string) ($f['date_from'] ?? ''));
        $dt = trim((string) ($f['date_to'] ?? ''));
        if ($df !== '') {
            $where[] = 'DATE(so.opened_at) >= :df';
            $params['df'] = $df;
        }
        if ($dt !== '') {
            $where[] = 'DATE(so.opened_at) <= :dt';
            $params['dt'] = $dt;
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(so.code LIKE :sq OR so.description LIKE :sq2 OR c.name LIKE :sq3)';
            $w = '%' . $q . '%';
            $params['sq'] = $w;
            $params['sq2'] = $w;
            $params['sq3'] = $w;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM service_orders so LEFT JOIN clients c ON c.id = so.client_id WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT so.*, c.name AS client_name, u.name AS assigned_name
                FROM service_orders so
                LEFT JOIN clients c ON c.id = so.client_id
                LEFT JOIN users u ON u.id = so.assigned_user_id
                WHERE {$whereSql}
                ORDER BY so.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function estoqueSaldos(int $companyId, ?int $storeId, ?int $categoryId, string $search, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['p.company_id = :cid', 'p.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($categoryId !== null && $categoryId > 0) {
            $where[] = 'p.category_id = :cat';
            $params['cat'] = $categoryId;
        }
        if ($search !== '') {
            $where[] = '(p.name LIKE :q OR p.sku LIKE :q2)';
            $w = '%' . $search . '%';
            $params['q'] = $w;
            $params['q2'] = $w;
        }
        $storeFilter = '';
        if ($storeId !== null && $storeId > 0) {
            $storeFilter = ' AND pss.store_id = :sid';
            $params['sid'] = $storeId;
        }
        $whereSql = implode(' AND ', $where);
        $from = "products p
            INNER JOIN product_store_stock pss ON pss.product_id = p.id AND pss.company_id = p.company_id {$storeFilter}
            LEFT JOIN product_categories pc ON pc.id = p.category_id
            LEFT JOIN stores st ON st.id = pss.store_id";
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$from} WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT p.id AS product_id, p.name AS product_name, p.sku, p.stock_min, pss.store_id, st.name AS store_name, pss.qty, pc.name AS category_name
                FROM {$from}
                WHERE {$whereSql}
                ORDER BY p.name ASC, st.name ASC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function estoqueAbaixoMinimo(int $companyId, ?int $storeId, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $params = ['cid' => $companyId];
        $storeSql = '';
        if ($storeId !== null && $storeId > 0) {
            $storeSql = ' AND pss.store_id = :sid';
            $params['sid'] = $storeId;
        }
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM products p
             INNER JOIN product_store_stock pss ON pss.product_id = p.id AND pss.company_id = p.company_id {$storeSql}
             WHERE p.company_id = :cid AND p.deleted_at IS NULL AND pss.qty < p.stock_min"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT p.id AS product_id, p.name, p.sku, p.stock_min, pss.qty AS qty_store, st.name AS store_name,
                (pss.qty < p.stock_min) AS below_min
                FROM products p
                INNER JOIN product_store_stock pss ON pss.product_id = p.id AND pss.company_id = p.company_id {$storeSql}
                LEFT JOIN stores st ON st.id = pss.store_id
                WHERE p.company_id = :cid AND p.deleted_at IS NULL AND pss.qty < p.stock_min
                ORDER BY (p.stock_min - pss.qty) DESC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array{ap_open: float, ar_open: float, ap_paid_period: float, ar_received_period: float}
     */
    public function financeiroTotais(int $companyId, ?string $df, ?string $dt): array
    {
        $params = ['cid' => $companyId];
        $apOpen = 0.0;
        $arOpen = 0.0;
        $stmt = $this->pdo()->prepare(
            "SELECT COALESCE(SUM(amount - paid_amount),0) FROM accounts_payable WHERE company_id = :cid AND status = 'open' AND deleted_at IS NULL"
        );
        $stmt->execute($params);
        $apOpen = (float) $stmt->fetchColumn();
        $stmt = $this->pdo()->prepare(
            "SELECT COALESCE(SUM(amount - paid_amount),0) FROM accounts_receivable WHERE company_id = :cid AND status = 'open' AND deleted_at IS NULL"
        );
        $stmt->execute($params);
        $arOpen = (float) $stmt->fetchColumn();

        $apPaid = 0.0;
        $arRec = 0.0;
        if ($df !== null && $df !== '' && $dt !== null && $dt !== '') {
            $stmt = $this->pdo()->prepare(
                "SELECT COALESCE(SUM(paid_amount),0) FROM accounts_payable WHERE company_id = :cid AND status = 'paid'
                 AND deleted_at IS NULL AND DATE(updated_at) BETWEEN :df AND :dt"
            );
            $stmt->execute(['cid' => $companyId, 'df' => $df, 'dt' => $dt]);
            $apPaid = (float) $stmt->fetchColumn();
            $stmt = $this->pdo()->prepare(
                "SELECT COALESCE(SUM(paid_amount),0) FROM accounts_receivable WHERE company_id = :cid AND status = 'paid'
                 AND deleted_at IS NULL AND DATE(updated_at) BETWEEN :df AND :dt"
            );
            $stmt->execute(['cid' => $companyId, 'df' => $df, 'dt' => $dt]);
            $arRec = (float) $stmt->fetchColumn();
        }

        return [
            'ap_open' => $apOpen,
            'ar_open' => $arOpen,
            'ap_paid_period' => $apPaid,
            'ar_received_period' => $arRec,
        ];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function financeiroTitulos(int $companyId, string $tipo, array $f, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        if ($tipo === 'pagar') {
            $where = ['ap.company_id = :cid', "ap.deleted_at IS NULL"];
            $params = ['cid' => $companyId];
            $st = (string) ($f['status'] ?? 'all');
            if ($st !== 'all' && $st !== '') {
                $where[] = 'ap.status = :st';
                $params['st'] = $st;
            }
            if (!empty($f['supplier_id'])) {
                $where[] = 'ap.supplier_id = :sup';
                $params['sup'] = (int) $f['supplier_id'];
            }
            $df = trim((string) ($f['date_from'] ?? ''));
            $dt = trim((string) ($f['date_to'] ?? ''));
            if ($df !== '') {
                $where[] = 'ap.due_date >= :df';
                $params['df'] = $df;
            }
            if ($dt !== '') {
                $where[] = 'ap.due_date <= :dt';
                $params['dt'] = $dt;
            }
            $whereSql = implode(' AND ', $where);
            $stmt = $this->pdo()->prepare(
                "SELECT COUNT(*) FROM accounts_payable ap WHERE {$whereSql}"
            );
            $stmt->execute($params);
            $total = (int) $stmt->fetchColumn();
            $sql = "SELECT 'pagar' AS tipo, ap.id, ap.description, ap.amount, ap.due_date, ap.status, ap.paid_amount, ap.created_at,
                    s.trade_name AS party_name
                    FROM accounts_payable ap
                    LEFT JOIN suppliers s ON s.id = ap.supplier_id
                    WHERE {$whereSql}
                    ORDER BY ap.due_date ASC
                    LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);

            return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
        }
        $where = ['ar.company_id = :cid', 'ar.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        $st = (string) ($f['status'] ?? 'all');
        if ($st !== 'all' && $st !== '') {
            $where[] = 'ar.status = :st';
            $params['st'] = $st;
        }
        if (!empty($f['client_id'])) {
            $where[] = 'ar.client_id = :cl';
            $params['cl'] = (int) $f['client_id'];
        }
        $df = trim((string) ($f['date_from'] ?? ''));
        $dt = trim((string) ($f['date_to'] ?? ''));
        if ($df !== '') {
            $where[] = 'ar.due_date >= :df';
            $params['df'] = $df;
        }
        if ($dt !== '') {
            $where[] = 'ar.due_date <= :dt';
            $params['dt'] = $dt;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM accounts_receivable ar WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();
        $sql = "SELECT 'receber' AS tipo, ar.id, ar.description, ar.amount, ar.due_date, ar.status, ar.paid_amount, ar.created_at,
                c.name AS party_name
                FROM accounts_receivable ar
                LEFT JOIN clients c ON c.id = ar.client_id
                WHERE {$whereSql}
                ORDER BY ar.due_date ASC
                LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array{ativos:int, Suspensos+cancelados - use status counts}
     */
    public function contratosSummary(int $companyId): array
    {
        $a = [
            'svc' => 0, 'rent' => 0, 'sub' => 0, 'svc_active' => 0,
            'ending_soon' => 0, 'suspended' => 0, 'cancelled' => 0,
        ];
        foreach ([['contract_services', 'svc'], ['contract_rentals', 'rent'], ['contract_subscriptions', 'sub']] as $pair) {
            $t = $pair[0];
            $k = $pair[1];
            if (!$this->tableExists($t)) {
                continue;
            }
            $del = $this->columnExists($t, 'deleted_at') ? ' AND deleted_at IS NULL' : '';
            $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$t} WHERE company_id = :cid {$del}");
            $stmt->execute(['cid' => $companyId]);
            $a[$k] = (int) $stmt->fetchColumn();
        }
        if ($this->tableExists('contract_services')) {
            $stmt = $this->pdo()->prepare(
                "SELECT COUNT(*) FROM contract_services WHERE company_id = :cid AND deleted_at IS NULL AND status = 'active'"
            );
            $stmt->execute(['cid' => $companyId]);
            $a['svc_active'] = (int) $stmt->fetchColumn();
        }
        foreach (['contract_services', 'contract_rentals', 'contract_subscriptions'] as $t) {
            if (!$this->tableExists($t)) {
                continue;
            }
            $del = $this->columnExists($t, 'deleted_at') ? ' AND deleted_at IS NULL' : '';
            $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM {$t} WHERE company_id = :cid {$del} AND status = 'suspended'");
            $stmt->execute(['cid' => $companyId]);
            $a['suspended'] += (int) $stmt->fetchColumn();
            $stmt = $this->pdo()->prepare(
                "SELECT COUNT(*) FROM {$t} WHERE company_id = :cid {$del} AND status IN ('cancelled','closed')"
            );
            $stmt->execute(['cid' => $companyId]);
            $a['cancelled'] += (int) $stmt->fetchColumn();
        }
        $soon = date('Y-m-d', strtotime('+30 days'));
        if ($this->tableExists('contract_rentals')) {
            $stmt = $this->pdo()->prepare(
                "SELECT COUNT(*) FROM contract_rentals WHERE company_id = :cid AND deleted_at IS NULL AND end_date IS NOT NULL AND end_date <= :soon AND end_date >= CURDATE()"
            );
            $stmt->execute(['cid' => $companyId, 'soon' => $soon]);
            $a['ending_soon'] += (int) $stmt->fetchColumn();
        }
        if ($this->tableExists('contract_services')) {
            $stmt = $this->pdo()->prepare(
                "SELECT COUNT(*) FROM contract_services WHERE company_id = :cid AND deleted_at IS NULL
                 AND end_date IS NOT NULL AND end_date <= :soon AND end_date >= CURDATE()"
            );
            $stmt->execute(['cid' => $companyId, 'soon' => $soon]);
            $a['ending_soon'] += (int) $stmt->fetchColumn();
        }

        return $a;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function contratosRows(int $companyId, string $ckind, array $f, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $parts = [];
        $params = ['cid' => $companyId];
        $clientId = !empty($f['client_id']) ? (int) $f['client_id'] : 0;
        if ($clientId > 0) {
            $params['cl'] = $clientId;
        }
        $svcCl = $clientId > 0 ? ' AND cs.client_id = :cl' : '';
        if ($ckind === 'all' || $ckind === 'service') {
            $parts[] = "SELECT 'service' AS ckind, cs.id, cs.contract_number AS ref_num, c.name AS client_name, cs.status, cs.amount, cs.start_date, cs.end_date, cs.created_at
                FROM contract_services cs
                LEFT JOIN clients c ON c.id = cs.client_id
                WHERE cs.company_id = :cid AND cs.deleted_at IS NULL{$svcCl}";
        }
        if ($ckind === 'all' || $ckind === 'rental') {
            $crCl = $clientId > 0 ? ' AND cr.client_id = :cl' : '';
            $parts[] = "SELECT 'rental' AS ckind, cr.id, cr.contract_number AS ref_num, c.name AS client_name, cr.status, cr.amount, cr.start_date, cr.end_date, cr.created_at
                FROM contract_rentals cr
                LEFT JOIN clients c ON c.id = cr.client_id
                WHERE cr.company_id = :cid AND cr.deleted_at IS NULL{$crCl}";
        }
        if ($ckind === 'all' || $ckind === 'subscription') {
            $csuCl = $clientId > 0 ? ' AND csu.client_id = :cl' : '';
            $parts[] = "SELECT 'subscription' AS ckind, csu.id, csu.subscription_number AS ref_num, c.name AS client_name, csu.status, csu.recurring_amount AS amount, csu.start_date, csu.next_billing_date AS end_date, csu.created_at
                FROM contract_subscriptions csu
                LEFT JOIN clients c ON c.id = csu.client_id
                WHERE csu.company_id = :cid AND csu.deleted_at IS NULL{$csuCl}";
        }
        if ($parts === []) {
            return ['rows' => [], 'total' => 0];
        }
        $union = implode(' UNION ALL ', $parts);
        $st = (string) ($f['status'] ?? 'all');
        $wrapWhere = '';
        if ($st !== 'all' && $st !== '') {
            $wrapWhere = ' WHERE status = :wst';
            $params['wst'] = $st;
        }
        $stmt = $this->pdo()->prepare("SELECT COUNT(*) FROM ({$union}) u {$wrapWhere}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT * FROM ({$union}) u {$wrapWhere} ORDER BY created_at DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * @return array{by_status: array<string, int>, total_amount: float}
     */
    public function notasFiscaisSummary(int $companyId, ?string $df, ?string $dt, ?string $kind): array
    {
        if (!$this->tableExists('fiscal_documents')) {
            return ['by_status' => [], 'total_amount' => 0.0];
        }
        $where = ['company_id = :cid', 'deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        if ($kind !== null && $kind !== '' && $kind !== 'all') {
            $where[] = 'document_kind = :dk';
            $params['dk'] = $kind;
        }
        if ($df !== null && $df !== '') {
            $where[] = 'DATE(COALESCE(issued_at, created_at)) >= :df';
            $params['df'] = $df;
        }
        if ($dt !== null && $dt !== '') {
            $where[] = 'DATE(COALESCE(issued_at, created_at)) <= :dt';
            $params['dt'] = $dt;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT status, COUNT(*) AS cnt, COALESCE(SUM(total_amount), 0) AS amt
             FROM fiscal_documents WHERE {$whereSql} GROUP BY status"
        );
        $stmt->execute($params);
        $byStatus = [];
        $totalAmt = 0.0;
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $byStatus[(string) $row['status']] = (int) $row['cnt'];
            $totalAmt += (float) $row['amt'];
        }

        return ['by_status' => $byStatus, 'total_amount' => $totalAmt];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function notasFiscaisRows(int $companyId, array $f, int $page, int $perPage): array
    {
        if (!$this->tableExists('fiscal_documents')) {
            return ['rows' => [], 'total' => 0];
        }
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = ['fd.company_id = :cid', 'fd.deleted_at IS NULL'];
        $params = ['cid' => $companyId];
        $kind = (string) ($f['kind'] ?? 'all');
        if ($kind !== 'all' && $kind !== '') {
            $where[] = 'fd.document_kind = :dk';
            $params['dk'] = $kind;
        }
        $st = (string) ($f['status'] ?? 'all');
        if ($st !== 'all' && $st !== '') {
            $where[] = 'fd.status = :st';
            $params['st'] = $st;
        }
        $df = trim((string) ($f['date_from'] ?? ''));
        $dt = trim((string) ($f['date_to'] ?? ''));
        if ($df !== '') {
            $where[] = 'DATE(COALESCE(fd.issued_at, fd.created_at)) >= :df';
            $params['df'] = $df;
        }
        if ($dt !== '') {
            $where[] = 'DATE(COALESCE(fd.issued_at, fd.created_at)) <= :dt';
            $params['dt'] = $dt;
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(fd.document_number LIKE :sq OR fd.access_key LIKE :sq2 OR c.name LIKE :sq3 OR sup.trade_name LIKE :sq4)';
            $w = '%' . $q . '%';
            $params['sq'] = $w;
            $params['sq2'] = $w;
            $params['sq3'] = $w;
            $params['sq4'] = $w;
        }
        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM fiscal_documents fd
             LEFT JOIN clients c ON c.id = fd.client_id
             LEFT JOIN suppliers sup ON sup.id = fd.supplier_id
             WHERE {$whereSql}"
        );
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT fd.*, c.name AS client_name, sup.trade_name AS supplier_name
                FROM fiscal_documents fd
                LEFT JOIN clients c ON c.id = fd.client_id
                LEFT JOIN suppliers sup ON sup.id = fd.supplier_id
                WHERE {$whereSql}
                ORDER BY fd.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    /**
     * Títulos em aberto com vencimento anterior à data informada (inadimplência / atraso).
     *
     * @return array{ap_overdue: float, ar_overdue: float, ap_overdue_count: int, ar_overdue_count: int}
     */
    public function financeiroVencidos(int $companyId, string $asOfDate): array
    {
        $params = ['cid' => $companyId, 'd' => $asOfDate];
        $stmt = $this->pdo()->prepare(
            "SELECT COALESCE(SUM(amount - paid_amount),0), COUNT(*) FROM accounts_payable
             WHERE company_id = :cid AND status = 'open' AND deleted_at IS NULL AND due_date < :d"
        );
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_NUM);

        $stmt = $this->pdo()->prepare(
            "SELECT COALESCE(SUM(amount - paid_amount),0), COUNT(*) FROM accounts_receivable
             WHERE company_id = :cid AND status = 'open' AND deleted_at IS NULL AND due_date < :d"
        );
        $stmt->execute($params);
        $r2 = $stmt->fetch(PDO::FETCH_NUM);

        return [
            'ap_overdue' => (float) ($r[0] ?? 0),
            'ap_overdue_count' => (int) ($r[1] ?? 0),
            'ar_overdue' => (float) ($r2[0] ?? 0),
            'ar_overdue_count' => (int) ($r2[1] ?? 0),
        ];
    }

    /**
     * Fluxo no período: recebimentos e pagamentos efetivados (por data de atualização do título).
     *
     * @return array{entradas: float, saidas: float, saldo: float}
     */
    public function financeiroFluxoPeriodo(int $companyId, string $df, string $dt): array
    {
        $stmt = $this->pdo()->prepare(
            "SELECT COALESCE(SUM(paid_amount),0) FROM accounts_receivable
             WHERE company_id = :cid AND deleted_at IS NULL AND status = 'paid'
             AND DATE(updated_at) BETWEEN :df AND :dt"
        );
        $stmt->execute(['cid' => $companyId, 'df' => $df, 'dt' => $dt]);
        $ent = (float) $stmt->fetchColumn();
        $stmt = $this->pdo()->prepare(
            "SELECT COALESCE(SUM(paid_amount),0) FROM accounts_payable
             WHERE company_id = :cid AND deleted_at IS NULL AND status = 'paid'
             AND DATE(updated_at) BETWEEN :df AND :dt"
        );
        $stmt->execute(['cid' => $companyId, 'df' => $df, 'dt' => $dt]);
        $sai = (float) $stmt->fetchColumn();

        return ['entradas' => $ent, 'saidas' => $sai, 'saldo' => $ent - $sai];
    }
}
