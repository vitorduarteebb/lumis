<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\ClientRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ReportsRepository;
use App\Repositories\StockMovementRepository;
use App\Repositories\StoreRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\UserRepository;

final class RelatoriosController extends Controller
{
    private const PER_PAGE = 15;

    public function cadastros(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $entity = (string) $request->input('entity', 'clients');
        if (!in_array($entity, ['clients', 'suppliers', 'employees', 'carriers'], true)) {
            $entity = 'clients';
        }
        $status = (string) $request->input('status', 'all');
        if (!in_array($status, ['all', 'active', 'inactive'], true)) {
            $status = 'all';
        }
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));
        $q = trim((string) $request->input('q', ''));
        $stForRows = $status === 'active' ? 'active' : ($status === 'inactive' ? 'inactive' : 'all');

        $repo = new ReportsRepository();
        $totals = $repo->cadastrosTotals($cid, [
            'status' => $status,
            'date_from' => $dateFrom !== '' ? $dateFrom : null,
            'date_to' => $dateTo !== '' ? $dateTo : null,
        ]);
        $rowsResult = $repo->cadastrosRows(
            $cid,
            $entity,
            $q,
            $stForRows,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $rowsResult['total'] > 0 ? max(1, (int) ceil($rowsResult['total'] / self::PER_PAGE)) : 1;

        return $this->view('relatorios/cadastros', [
            'title' => 'Relatórios — Cadastros',
            'pageTitle' => 'Relatórios · Cadastros',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => '/relatorios/cadastros'],
                ['label' => 'Cadastros', 'href' => null],
            ],
            'totals' => $totals,
            'rows' => $rowsResult['rows'],
            'total' => $rowsResult['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'entity' => $entity,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $q,
            'basePath' => '/relatorios/cadastros',
            'queryParams' => array_filter([
                'entity' => $entity !== 'clients' ? $entity : null,
                'status' => $status !== 'all' ? $status : null,
                'date_from' => $dateFrom !== '' ? $dateFrom : null,
                'date_to' => $dateTo !== '' ? $dateTo : null,
                'q' => $q !== '' ? $q : null,
            ], static fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    public function vendas(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $kind = (string) $request->input('kind', 'product');
        if (!in_array($kind, ['all', 'product', 'balcao', 'service'], true)) {
            $kind = 'product';
        }
        $f = [
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
            'client_id' => (int) $request->input('client_id', 0),
            'store_id' => (int) $request->input('store_id', 0),
            'seller_user_id' => (int) $request->input('seller_user_id', 0),
            'status' => (string) $request->input('status', 'all'),
            'q' => trim((string) $request->input('q', '')),
        ];
        if ($f['client_id'] < 1) {
            $f['client_id'] = 0;
        }
        if ($f['store_id'] < 1) {
            $f['store_id'] = 0;
        }
        if ($f['seller_user_id'] < 1) {
            $f['seller_user_id'] = 0;
        }

        $repo = new ReportsRepository();
        $summary = $repo->vendasSummary($cid, $kind, $f);
        $rowsResult = $repo->vendasRows($cid, $kind, $f, $page, self::PER_PAGE);
        $totalPages = $rowsResult['total'] > 0 ? max(1, (int) ceil($rowsResult['total'] / self::PER_PAGE)) : 1;

        $sumProd = $repo->vendasSummary($cid, 'product', $f);
        $sumBal = $repo->vendasSummary($cid, 'balcao', $f);
        $sumSvc = $repo->vendasSummary($cid, 'service', $f);

        $stores = (new StoreRepository())->byCompanyId($cid);
        $clients = (new ClientRepository())->listForSelect($cid);
        $sellers = (new UserRepository())->listActiveForCompany($cid);

        return $this->view('relatorios/vendas', [
            'title' => 'Relatórios — Vendas',
            'pageTitle' => 'Relatórios · Vendas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => '/relatorios/vendas'],
                ['label' => 'Vendas', 'href' => null],
            ],
            'summary' => $summary,
            'sumProd' => $sumProd,
            'sumBal' => $sumBal,
            'sumSvc' => $sumSvc,
            'rows' => $rowsResult['rows'],
            'total' => $rowsResult['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'kind' => $kind,
            'dateFrom' => $f['date_from'],
            'dateTo' => $f['date_to'],
            'clientId' => $f['client_id'],
            'storeId' => $f['store_id'],
            'sellerUserId' => $f['seller_user_id'],
            'statusFilter' => $f['status'],
            'search' => $f['q'],
            'stores' => $stores,
            'clients' => $clients,
            'sellers' => $sellers,
            'basePath' => '/relatorios/vendas',
            'queryParams' => array_filter([
                'kind' => $kind !== 'product' ? $kind : null,
                'date_from' => $f['date_from'] !== '' ? $f['date_from'] : null,
                'date_to' => $f['date_to'] !== '' ? $f['date_to'] : null,
                'client_id' => $f['client_id'] > 0 ? $f['client_id'] : null,
                'store_id' => $f['store_id'] > 0 ? $f['store_id'] : null,
                'seller_user_id' => $f['seller_user_id'] > 0 ? $f['seller_user_id'] : null,
                'status' => $f['status'] !== 'all' ? $f['status'] : null,
                'q' => $f['q'] !== '' ? $f['q'] : null,
            ], static fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    public function ordensServico(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $f = [
            'status' => (string) $request->input('status', 'all'),
            'assigned_user_id' => (int) $request->input('assigned_user_id', 0),
            'client_id' => (int) $request->input('client_id', 0),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
            'q' => trim((string) $request->input('q', '')),
        ];
        if ($f['assigned_user_id'] < 1) {
            $f['assigned_user_id'] = 0;
        }
        if ($f['client_id'] < 1) {
            $f['client_id'] = 0;
        }

        $repo = new ReportsRepository();
        $osSummary = $repo->ordensServicoSummary($cid, $f);
        $rowsResult = $repo->ordensServicoRows($cid, $f, $page, self::PER_PAGE);
        $totalPages = $rowsResult['total'] > 0 ? max(1, (int) ceil($rowsResult['total'] / self::PER_PAGE)) : 1;

        $techs = (new UserRepository())->listActiveForCompany($cid);
        $clients = (new ClientRepository())->listForSelect($cid);

        return $this->view('relatorios/ordens_servico', [
            'title' => 'Relatórios — Ordens de serviço',
            'pageTitle' => 'Relatórios · Ordens de serviço',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => '/relatorios/ordens-servico'],
                ['label' => 'Ordens de serviço', 'href' => null],
            ],
            'osSummary' => $osSummary,
            'rows' => $rowsResult['rows'],
            'total' => $rowsResult['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'statusFilter' => $f['status'],
            'assignedUserId' => $f['assigned_user_id'],
            'clientId' => $f['client_id'],
            'dateFrom' => $f['date_from'],
            'dateTo' => $f['date_to'],
            'search' => $f['q'],
            'techs' => $techs,
            'clients' => $clients,
            'basePath' => '/relatorios/ordens-servico',
            'queryParams' => array_filter([
                'status' => $f['status'] !== 'all' ? $f['status'] : null,
                'assigned_user_id' => $f['assigned_user_id'] > 0 ? $f['assigned_user_id'] : null,
                'client_id' => $f['client_id'] > 0 ? $f['client_id'] : null,
                'date_from' => $f['date_from'] !== '' ? $f['date_from'] : null,
                'date_to' => $f['date_to'] !== '' ? $f['date_to'] : null,
                'q' => $f['q'] !== '' ? $f['q'] : null,
            ], static fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    public function estoque(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $view = (string) $request->input('view', 'saldos');
        if (!in_array($view, ['saldos', 'movimentos', 'abaixo'], true)) {
            $view = 'saldos';
        }
        $storeId = (int) $request->input('store_id', 0);
        if ($storeId < 1) {
            $storeId = 0;
        }
        $categoryId = (int) $request->input('category_id', 0);
        if ($categoryId < 1) {
            $categoryId = 0;
        }
        $productId = (int) $request->input('product_id', 0);
        if ($productId < 1) {
            $productId = 0;
        }
        $search = trim((string) $request->input('q', ''));
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));
        $movType = (string) $request->input('movement_type', 'all');

        $repo = new ReportsRepository();
        $stockRepo = new StockMovementRepository();

        $stores = (new StoreRepository())->byCompanyId($cid);
        $categories = (new ProductRepository())->categoriesForCompany($cid);
        $products = (new ProductRepository())->listForSelect($cid);

        $rows = [];
        $total = 0;
        $belowCount = 0;

        if ($view === 'saldos') {
            $r = $repo->estoqueSaldos(
                $cid,
                $storeId > 0 ? $storeId : null,
                $categoryId > 0 ? $categoryId : null,
                $search,
                $page,
                self::PER_PAGE
            );
            $rows = $r['rows'];
            $total = $r['total'];
            $bc = $repo->estoqueAbaixoMinimo($cid, $storeId > 0 ? $storeId : null, 1, 1);
            $belowCount = (int) ($bc['total'] ?? 0);
        } elseif ($view === 'abaixo') {
            $r = $repo->estoqueAbaixoMinimo($cid, $storeId > 0 ? $storeId : null, $page, self::PER_PAGE);
            $rows = $r['rows'];
            $total = $r['total'];
        } else {
            $r = $stockRepo->paginate(
                $cid,
                $search,
                $movType,
                $productId > 0 ? $productId : null,
                $storeId > 0 ? $storeId : null,
                $dateFrom !== '' ? $dateFrom : null,
                $dateTo !== '' ? $dateTo : null,
                $page,
                self::PER_PAGE
            );
            $rows = $r['rows'];
            $total = $r['total'];
        }

        $totalPages = $total > 0 ? max(1, (int) ceil($total / self::PER_PAGE)) : 1;

        return $this->view('relatorios/estoque', [
            'title' => 'Relatórios — Estoque',
            'pageTitle' => 'Relatórios · Estoque',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => '/relatorios/estoque'],
                ['label' => 'Estoque', 'href' => null],
            ],
            'view' => $view,
            'rows' => $rows,
            'total' => $total,
            'belowCount' => $belowCount,
            'page' => $page,
            'totalPages' => $totalPages,
            'storeId' => $storeId,
            'categoryId' => $categoryId,
            'productId' => $productId,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'movementType' => $movType,
            'stores' => $stores,
            'categories' => $categories,
            'products' => $products,
            'basePath' => '/relatorios/estoque',
            'queryParams' => array_filter([
                'view' => $view !== 'saldos' ? $view : null,
                'store_id' => $storeId > 0 ? $storeId : null,
                'category_id' => $categoryId > 0 ? $categoryId : null,
                'product_id' => $productId > 0 ? $productId : null,
                'q' => $search !== '' ? $search : null,
                'date_from' => $dateFrom !== '' ? $dateFrom : null,
                'date_to' => $dateTo !== '' ? $dateTo : null,
                'movement_type' => $movType !== '' && $movType !== 'all' ? $movType : null,
            ], static fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    public function financeiro(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $tab = (string) $request->input('tab', 'pagar');
        if (!in_array($tab, ['pagar', 'receber'], true)) {
            $tab = 'pagar';
        }
        $df = trim((string) $request->input('date_from', ''));
        $dt = trim((string) $request->input('date_to', ''));
        $st = (string) $request->input('status', 'all');
        $supplierId = (int) $request->input('supplier_id', 0);
        $clientId = (int) $request->input('client_id', 0);
        if ($supplierId < 1) {
            $supplierId = 0;
        }
        if ($clientId < 1) {
            $clientId = 0;
        }

        $f = [
            'status' => $st,
            'supplier_id' => $supplierId,
            'client_id' => $clientId,
            'date_from' => $df !== '' ? $df : null,
            'date_to' => $dt !== '' ? $dt : null,
        ];

        $repo = new ReportsRepository();
        $tot = $repo->financeiroTotais($cid, $df !== '' ? $df : null, $dt !== '' ? $dt : null);
        $venc = $repo->financeiroVencidos($cid, (new \DateTimeImmutable('today'))->format('Y-m-d'));
        $fluxo = ($df !== '' && $dt !== '')
            ? $repo->financeiroFluxoPeriodo($cid, $df, $dt)
            : ['entradas' => 0.0, 'saidas' => 0.0, 'saldo' => 0.0];

        $tipo = $tab === 'pagar' ? 'pagar' : 'receber';
        $rowsResult = $repo->financeiroTitulos($cid, $tipo, $f, $page, self::PER_PAGE);
        $totalPages = $rowsResult['total'] > 0 ? max(1, (int) ceil($rowsResult['total'] / self::PER_PAGE)) : 1;

        $suppliers = (new SupplierRepository())->listForSelect($cid);
        $clients = (new ClientRepository())->listForSelect($cid);

        return $this->view('relatorios/financeiro', [
            'title' => 'Relatórios — Financeiro',
            'pageTitle' => 'Relatórios · Financeiro',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => '/relatorios/financeiro'],
                ['label' => 'Financeiro', 'href' => null],
            ],
            'tot' => $tot,
            'venc' => $venc,
            'fluxo' => $fluxo,
            'tab' => $tab,
            'rows' => $rowsResult['rows'],
            'total' => $rowsResult['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'dateFrom' => $df,
            'dateTo' => $dt,
            'statusFilter' => $st,
            'supplierId' => $supplierId,
            'clientId' => $clientId,
            'suppliers' => $suppliers,
            'clients' => $clients,
            'basePath' => '/relatorios/financeiro',
            'queryParams' => array_filter([
                'tab' => $tab !== 'pagar' ? $tab : null,
                'date_from' => $df !== '' ? $df : null,
                'date_to' => $dt !== '' ? $dt : null,
                'status' => $st !== 'all' ? $st : null,
                'supplier_id' => $supplierId > 0 ? $supplierId : null,
                'client_id' => $clientId > 0 ? $clientId : null,
            ], static fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    public function contratos(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $ckind = (string) $request->input('ckind', 'all');
        if (!in_array($ckind, ['all', 'service', 'rental', 'subscription'], true)) {
            $ckind = 'all';
        }
        $f = [
            'status' => (string) $request->input('status', 'all'),
            'client_id' => (int) $request->input('client_id', 0),
        ];
        if ($f['client_id'] < 1) {
            $f['client_id'] = 0;
        }

        $repo = new ReportsRepository();
        $cSum = $repo->contratosSummary($cid);
        $rowsResult = $repo->contratosRows($cid, $ckind, $f, $page, self::PER_PAGE);
        $totalPages = $rowsResult['total'] > 0 ? max(1, (int) ceil($rowsResult['total'] / self::PER_PAGE)) : 1;

        $clients = (new ClientRepository())->listForSelect($cid);

        return $this->view('relatorios/contratos', [
            'title' => 'Relatórios — Contratos',
            'pageTitle' => 'Relatórios · Contratos',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => '/relatorios/contratos'],
                ['label' => 'Contratos', 'href' => null],
            ],
            'cSum' => $cSum,
            'rows' => $rowsResult['rows'],
            'total' => $rowsResult['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'ckind' => $ckind,
            'statusFilter' => $f['status'],
            'clientId' => $f['client_id'],
            'clients' => $clients,
            'basePath' => '/relatorios/contratos',
            'queryParams' => array_filter([
                'ckind' => $ckind !== 'all' ? $ckind : null,
                'status' => $f['status'] !== 'all' ? $f['status'] : null,
                'client_id' => $f['client_id'] > 0 ? $f['client_id'] : null,
            ], static fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    public function notasFiscais(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $df = trim((string) $request->input('date_from', ''));
        $dt = trim((string) $request->input('date_to', ''));
        $kind = (string) $request->input('kind', 'all');
        $f = [
            'kind' => $kind,
            'status' => (string) $request->input('status', 'all'),
            'date_from' => $df !== '' ? $df : null,
            'date_to' => $dt !== '' ? $dt : null,
            'q' => trim((string) $request->input('q', '')),
        ];

        $repo = new ReportsRepository();
        $nfSum = $repo->notasFiscaisSummary($cid, $df !== '' ? $df : null, $dt !== '' ? $dt : null, $kind !== 'all' ? $kind : null);
        $rowsResult = $repo->notasFiscaisRows($cid, $f, $page, self::PER_PAGE);
        $totalPages = $rowsResult['total'] > 0 ? max(1, (int) ceil($rowsResult['total'] / self::PER_PAGE)) : 1;

        return $this->view('relatorios/notas_fiscais', [
            'title' => 'Relatórios — Notas fiscais',
            'pageTitle' => 'Relatórios · Notas fiscais',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => '/relatorios/notas-fiscais'],
                ['label' => 'Notas fiscais', 'href' => null],
            ],
            'nfSum' => $nfSum,
            'rows' => $rowsResult['rows'],
            'total' => $rowsResult['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'dateFrom' => $df,
            'dateTo' => $dt,
            'kind' => $kind,
            'statusFilter' => $f['status'],
            'search' => $f['q'],
            'basePath' => '/relatorios/notas-fiscais',
            'queryParams' => array_filter([
                'date_from' => $df !== '' ? $df : null,
                'date_to' => $dt !== '' ? $dt : null,
                'kind' => $kind !== 'all' ? $kind : null,
                'status' => $f['status'] !== 'all' ? $f['status'] : null,
                'q' => $f['q'] !== '' ? $f['q'] : null,
            ], static fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    public function logsSistema(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'action' => trim((string) $request->input('action', '')),
            'module' => trim((string) $request->input('module', '')),
            'user_id' => (int) $request->input('user_id', 0),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];
        if ($filters['user_id'] < 1) {
            $filters['user_id'] = 0;
        }

        $repo = new AuditLogRepository();
        $rowsResult = $repo->paginateForCompany($cid, $filters, $page, self::PER_PAGE);
        $totalPages = $rowsResult['total'] > 0 ? max(1, (int) ceil($rowsResult['total'] / self::PER_PAGE)) : 1;
        $userOpts = $repo->usersForFilter($cid);

        return $this->view('relatorios/logs_sistema', [
            'title' => 'Relatórios — Logs do sistema',
            'pageTitle' => 'Relatórios · Logs do sistema',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => '/relatorios/logs-sistema'],
                ['label' => 'Logs do sistema', 'href' => null],
            ],
            'rows' => $rowsResult['rows'],
            'total' => $rowsResult['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $filters['q'],
            'action' => $filters['action'],
            'module' => $filters['module'],
            'userId' => $filters['user_id'],
            'dateFrom' => $filters['date_from'],
            'dateTo' => $filters['date_to'],
            'userOpts' => $userOpts,
            'basePath' => '/relatorios/logs-sistema',
            'queryParams' => array_filter([
                'q' => $filters['q'] !== '' ? $filters['q'] : null,
                'action' => $filters['action'] !== '' ? $filters['action'] : null,
                'module' => $filters['module'] !== '' ? $filters['module'] : null,
                'user_id' => $filters['user_id'] > 0 ? $filters['user_id'] : null,
                'date_from' => $filters['date_from'] !== '' ? $filters['date_from'] : null,
                'date_to' => $filters['date_to'] !== '' ? $filters['date_to'] : null,
            ], static fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    private function requireCompany(): int
    {
        $cid = current_company_id();
        if ($cid === null) {
            Session::flash('error', 'Empresa não definida.');
            redirect('/dashboard');
        }

        return $cid;
    }
}
