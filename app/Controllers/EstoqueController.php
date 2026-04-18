<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ProductRepository;
use App\Repositories\ProductStoreStockRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\StockAdjustmentRepository;
use App\Repositories\StockMovementRepository;
use App\Repositories\StockReturnRepository;
use App\Repositories\StockTransferRepository;
use App\Repositories\SupplierQuoteRepository;
use App\Repositories\SupplierRepository;
use App\Services\EstoqueOperacaoService;
use App\Services\InventoryStockService;

final class EstoqueController extends Controller
{
    private const PER_PAGE = 15;

    public function movimentacoes(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $repo = new StockMovementRepository();
        $result = $repo->paginate(
            $cid,
            trim((string) $request->input('q', '')),
            (string) $request->input('type', 'all'),
            ((int) $request->input('product_id', 0)) > 0 ? (int) $request->input('product_id') : null,
            ((int) $request->input('store_id', 0)) > 0 ? (int) $request->input('store_id') : null,
            trim((string) $request->input('date_from', '')) ?: null,
            trim((string) $request->input('date_to', '')) ?: null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 1000)['rows'];
        $stores = (new \App\Repositories\StoreRepository())->byCompanyId($cid);

        return $this->view('estoque/movimentacoes/index', [
            'title' => 'Movimentações',
            'pageTitle' => 'Movimentações de estoque',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Movimentações', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'basePath' => '/estoque/movimentacoes',
            'queryParams' => array_filter([
                'q' => trim((string) $request->input('q', '')) !== '' ? trim((string) $request->input('q', '')) : null,
                'type' => (string) $request->input('type', 'all') !== 'all' ? (string) $request->input('type', 'all') : null,
                'product_id' => ((int) $request->input('product_id', 0)) > 0 ? (int) $request->input('product_id') : null,
                'store_id' => ((int) $request->input('store_id', 0)) > 0 ? (int) $request->input('store_id') : null,
                'date_from' => trim((string) $request->input('date_from', '')) ?: null,
                'date_to' => trim((string) $request->input('date_to', '')) ?: null,
            ]),
            'products' => $products,
            'stores' => $stores,
            'search' => trim((string) $request->input('q', '')),
            'typeFilter' => (string) $request->input('type', 'all'),
            'filterProductId' => ((int) $request->input('product_id', 0)) > 0 ? (int) $request->input('product_id') : 0,
            'filterStoreId' => ((int) $request->input('store_id', 0)) > 0 ? (int) $request->input('store_id') : 0,
            'dateFrom' => trim((string) $request->input('date_from', '')),
            'dateTo' => trim((string) $request->input('date_to', '')),
        ]);
    }

    public function movimentacaoShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/estoque/movimentacoes');
        }
        $row = (new StockMovementRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Movimentação não encontrada.');
            redirect('/estoque/movimentacoes');
        }

        return $this->view('estoque/movimentacoes/show', [
            'title' => 'Movimentação #' . $id,
            'pageTitle' => 'Detalhe da movimentação',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Movimentações', 'href' => '/estoque/movimentacoes'],
                ['label' => '#' . $id, 'href' => null],
            ],
            'row' => $row,
        ]);
    }

    public function ajustes(Request $request): string
    {
        $cid = $this->requireCompany();
        $repo = new StockAdjustmentRepository();
        $page = max(1, (int) $request->input('page', 1));
        $result = $repo->paginate(
            $cid,
            ((int) $request->input('product_id', 0)) > 0 ? (int) $request->input('product_id') : null,
            trim((string) $request->input('date_from', '')) ?: null,
            trim((string) $request->input('date_to', '')) ?: null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 800)['rows'];

        return $this->view('estoque/ajustes/index', [
            'title' => 'Ajustes',
            'pageTitle' => 'Ajustes de estoque',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Ajustes', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'basePath' => '/estoque/ajustes',
            'queryParams' => array_filter([
                'product_id' => ((int) $request->input('product_id', 0)) > 0 ? (int) $request->input('product_id') : null,
                'date_from' => trim((string) $request->input('date_from', '')) ?: null,
                'date_to' => trim((string) $request->input('date_to', '')) ?: null,
            ]),
            'products' => $products,
            'filterProductId' => ((int) $request->input('product_id', 0)) > 0 ? (int) $request->input('product_id') : 0,
            'dateFrom' => trim((string) $request->input('date_from', '')),
            'dateTo' => trim((string) $request->input('date_to', '')),
        ]);
    }

    public function ajustesNovo(Request $request): string
    {
        $cid = $this->requireCompany();
        $stores = (new \App\Repositories\StoreRepository())->byCompanyId($cid);
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 2000)['rows'];

        return $this->view('estoque/ajustes/form', [
            'title' => 'Novo ajuste',
            'pageTitle' => 'Novo ajuste',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Ajustes', 'href' => '/estoque/ajustes'],
                ['label' => 'Novo', 'href' => null],
            ],
            'stores' => $stores,
            'products' => $products,
            'errors' => [],
        ]);
    }

    public function ajustesStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/estoque/ajustes/novo');
        }
        $storeId = (int) $request->input('store_id', 0);
        $pid = (int) $request->input('product_id', 0);
        $dir = (string) $request->input('direction', 'in');
        $qty = (float) str_replace(',', '.', (string) $request->input('qty', '0'));
        if ($storeId < 1 || $pid < 1 || $qty <= 0 || !in_array($dir, ['in', 'out'], true)) {
            Session::flash('error', 'Preencha loja, produto, direção e quantidade válidos.');
            redirect('/estoque/ajustes/novo');
        }
        $pdo = Database::connection();
        $pss = new ProductStoreStockRepository();
        $svc = new InventoryStockService($pdo, $pss);
        $pdo->beginTransaction();
        try {
            $adjId = (new StockAdjustmentRepository())->insert($cid, [
                'store_id' => $storeId,
                'product_id' => $pid,
                'direction' => $dir,
                'qty' => $qty,
                'reason_text' => trim((string) $request->input('reason_text', '')) ?: null,
                'notes' => trim((string) $request->input('notes', '')) ?: null,
                'created_by' => auth_id(),
            ]);
            $signed = $dir === 'in' ? $qty : -$qty;
            $svc->applyMovement(
                $cid,
                $storeId,
                $pid,
                $signed,
                'adjust',
                'stock_adjustments',
                $adjId,
                auth_id(),
                trim((string) $request->input('notes', '')) ?: null,
                'AJ-' . $adjId
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Session::flash('error', $e->getMessage());
            redirect('/estoque/ajustes/novo');
        }
        Session::flash('success', 'Ajuste registrado.');

        redirect('/estoque/ajustes/' . $adjId);
    }

    public function ajusteShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/ajustes');
        }
        $row = (new StockAdjustmentRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Ajuste não encontrado.');
            redirect('/estoque/ajustes');
        }

        return $this->view('estoque/ajustes/show', [
            'title' => 'Ajuste #' . $id,
            'pageTitle' => 'Ajuste de estoque',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Ajustes', 'href' => '/estoque/ajustes'],
                ['label' => '#' . $id, 'href' => null],
            ],
            'row' => $row,
        ]);
    }

    public function transferencias(Request $request): string
    {
        $cid = $this->requireCompany();
        $st = (string) $request->input('status', 'all');
        $page = max(1, (int) $request->input('page', 1));
        $result = (new StockTransferRepository())->paginate($cid, $st, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;

        return $this->view('estoque/transferencias/index', [
            'title' => 'Transferências',
            'pageTitle' => 'Transferências',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Transferências', 'href' => null],
            ],
            'rows' => $result['rows'],
            'status' => $st,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $result['total'],
            'basePath' => '/estoque/transferencias',
            'queryParams' => $st !== '' && $st !== 'all' ? ['status' => $st] : [],
        ]);
    }

    public function transferenciasNovo(Request $request): string
    {
        $cid = $this->requireCompany();
        $stores = (new \App\Repositories\StoreRepository())->allForCompany($cid);
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 2000)['rows'];

        return $this->view('estoque/transferencias/form', [
            'title' => 'Nova transferência',
            'pageTitle' => 'Nova transferência',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Transferências', 'href' => '/estoque/transferencias'],
                ['label' => 'Nova', 'href' => null],
            ],
            'stores' => $stores,
            'products' => $products,
        ]);
    }

    public function transferenciasStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/estoque/transferencias/novo');
        }
        $from = (int) $request->input('from_store_id', 0);
        $to = (int) $request->input('to_store_id', 0);
        if ($from < 1 || $to < 1 || $from === $to) {
            Session::flash('error', 'Selecione lojas de origem e destino diferentes.');
            redirect('/estoque/transferencias/novo');
        }
        $raw = $request->input('items', []);
        $items = [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $pid = (int) ($row['product_id'] ?? 0);
                $qty = (float) str_replace(',', '.', (string) ($row['qty'] ?? 0));
                if ($pid > 0 && $qty > 0) {
                    $items[] = ['product_id' => $pid, 'qty' => $qty];
                }
            }
        }
        if ($items === []) {
            Session::flash('error', 'Inclua ao menos um item com quantidade.');
            redirect('/estoque/transferencias/novo');
        }
        $tid = (new StockTransferRepository())->create(
            $cid,
            $from,
            $to,
            trim((string) $request->input('notes', '')) ?: null,
            auth_id(),
            $items
        );
        Session::flash('success', 'Transferência criada (pendente). Conclua quando os produtos saírem.');

        redirect('/estoque/transferencias/' . $tid);
    }

    public function transferenciaShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/transferencias');
        }
        $bundle = (new StockTransferRepository())->findWithItems($id, $cid);
        if ($bundle === null) {
            Session::flash('error', 'Transferência não encontrada.');
            redirect('/estoque/transferencias');
        }

        return $this->view('estoque/transferencias/show', [
            'title' => 'Transferência #' . $id,
            'pageTitle' => 'Transferência',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Transferências', 'href' => '/estoque/transferencias'],
                ['label' => '#' . $id, 'href' => null],
            ],
            'bundle' => $bundle,
        ]);
    }

    public function transferenciaConcluir(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/estoque/transferencias');
        }
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/transferencias');
        }
        try {
            EstoqueOperacaoService::make()->completeTransfer($id, $cid, (int) auth_id());
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect('/estoque/transferencias/' . $id);
        }
        Session::flash('success', 'Transferência concluída.');

        redirect('/estoque/transferencias/' . $id);
    }

    public function cotacoes(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $result = (new SupplierQuoteRepository())->paginate(
            $cid,
            trim((string) $request->input('q', '')),
            (string) $request->input('status', 'all'),
            trim((string) $request->input('date_from', '')) ?: null,
            trim((string) $request->input('date_to', '')) ?: null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;

        return $this->view('estoque/cotacoes/index', [
            'title' => 'Cotações',
            'pageTitle' => 'Cotações de compra',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Cotações', 'href' => null],
            ],
            'rows' => $result['rows'],
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $result['total'],
            'basePath' => '/estoque/cotacoes',
            'queryParams' => array_filter([
                'q' => trim((string) $request->input('q', '')) !== '' ? trim((string) $request->input('q', '')) : null,
                'status' => (string) $request->input('status', 'all') !== 'all' ? (string) $request->input('status', 'all') : null,
                'date_from' => trim((string) $request->input('date_from', '')) ?: null,
                'date_to' => trim((string) $request->input('date_to', '')) ?: null,
            ]),
            'search' => trim((string) $request->input('q', '')),
            'statusFilter' => (string) $request->input('status', 'all'),
            'dateFrom' => trim((string) $request->input('date_from', '')),
            'dateTo' => trim((string) $request->input('date_to', '')),
        ]);
    }

    public function cotacoesNovo(Request $request): string
    {
        return $this->cotacaoForm($request, 'create');
    }

    public function cotacoesStore(Request $request): string
    {
        return $this->cotacaoSave($request, 'create');
    }

    public function cotacaoShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/cotacoes');
        }
        $bundle = (new SupplierQuoteRepository())->findWithLines($id, $cid);
        if ($bundle === null) {
            Session::flash('error', 'Cotação não encontrada.');
            redirect('/estoque/cotacoes');
        }

        return $this->view('estoque/cotacoes/show', [
            'title' => 'Cotação',
            'pageTitle' => (string) ($bundle['quote']['quote_number'] ?? 'Cotação'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Cotações', 'href' => '/estoque/cotacoes'],
                ['label' => (string) ($bundle['quote']['quote_number'] ?? ''), 'href' => null],
            ],
            'bundle' => $bundle,
        ]);
    }

    public function cotacaoEditar(Request $request): string
    {
        return $this->cotacaoForm($request, 'edit');
    }

    public function cotacaoUpdate(Request $request): string
    {
        return $this->cotacaoSave($request, 'edit');
    }

    public function cotacaoCancelar(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/estoque/cotacoes');
        }
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/cotacoes');
        }
        (new SupplierQuoteRepository())->softDelete($id, $cid);
        Session::flash('success', 'Cotação cancelada.');

        redirect('/estoque/cotacoes');
    }

    private function cotacaoForm(Request $request, string $mode): string
    {
        $cid = $this->requireCompany();
        $suppliers = (new SupplierRepository())->paginate($cid, '', null, 1, 3000)['rows'];
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 3000)['rows'];
        $bundle = null;
        if ($mode === 'edit') {
            $id = $request->routeInt('id');
            if ($id === null) {
                redirect('/estoque/cotacoes');
            }
            $bundle = (new SupplierQuoteRepository())->findWithLines($id, $cid);
            if ($bundle === null || (string) ($bundle['quote']['status'] ?? '') === 'cancelled') {
                Session::flash('error', 'Cotação não encontrada.');
                redirect('/estoque/cotacoes');
            }
        }

        return $this->view('estoque/cotacoes/form', [
            'title' => $mode === 'edit' ? 'Editar cotação' : 'Nova cotação',
            'pageTitle' => 'Cotação de compra',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Cotações', 'href' => '/estoque/cotacoes'],
                ['label' => $mode === 'edit' ? 'Editar' : 'Nova', 'href' => null],
            ],
            'mode' => $mode,
            'suppliers' => $suppliers,
            'products' => $products,
            'bundle' => $bundle,
        ]);
    }

    private function cotacaoSave(Request $request, string $mode): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/estoque/cotacoes');
        }
        $lines = $this->parsePoLikeLines($request);
        if ($lines === []) {
            Session::flash('error', 'Inclua linhas.');
            redirect($mode === 'edit' ? '/estoque/cotacoes/' . $request->routeInt('id') . '/editar' : '/estoque/cotacoes/novo');
        }
        $sub = 0.0;
        foreach ($lines as $ln) {
            $sub += (float) $ln['line_total'];
        }
        $header = [
            'supplier_id' => (int) $request->input('supplier_id', 0),
            'quote_number' => trim((string) $request->input('quote_number', '')) ?: null,
            'status' => (string) $request->input('status', 'open'),
            'quoted_at' => trim((string) $request->input('quoted_at', date('Y-m-d'))),
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'total_amount' => $sub,
        ];
        if ($header['supplier_id'] < 1) {
            Session::flash('error', 'Selecione o fornecedor.');
            redirect('/estoque/cotacoes/novo');
        }
        $repo = new SupplierQuoteRepository();
        if ($mode === 'create') {
            $id = $repo->create($cid, $header, $lines, auth_id());
            Session::flash('success', 'Cotação criada.');

            redirect('/estoque/cotacoes/' . $id);
        }
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/cotacoes');
        }
        $repo->updateHeader($id, $cid, $header, auth_id());
        $repo->replaceLines($id, $lines);
        Session::flash('success', 'Cotação atualizada.');

        redirect('/estoque/cotacoes/' . $id);
    }

    public function compras(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $result = (new PurchaseOrderRepository())->paginate(
            $cid,
            trim((string) $request->input('q', '')),
            (string) $request->input('status', 'all'),
            trim((string) $request->input('date_from', '')) ?: null,
            trim((string) $request->input('date_to', '')) ?: null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;

        return $this->view('estoque/compras/index', [
            'title' => 'Compras',
            'pageTitle' => 'Compras',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Compras', 'href' => null],
            ],
            'rows' => $result['rows'],
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $result['total'],
            'basePath' => '/estoque/compras',
            'queryParams' => array_filter([
                'q' => trim((string) $request->input('q', '')) !== '' ? trim((string) $request->input('q', '')) : null,
                'status' => (string) $request->input('status', 'all') !== 'all' ? (string) $request->input('status', 'all') : null,
                'date_from' => trim((string) $request->input('date_from', '')) ?: null,
                'date_to' => trim((string) $request->input('date_to', '')) ?: null,
            ]),
            'search' => trim((string) $request->input('q', '')),
            'statusFilter' => (string) $request->input('status', 'all'),
            'dateFrom' => trim((string) $request->input('date_from', '')),
            'dateTo' => trim((string) $request->input('date_to', '')),
        ]);
    }

    public function comprasNovo(Request $request): string
    {
        return $this->compraForm($request, 'create');
    }

    public function comprasStore(Request $request): string
    {
        return $this->compraSave($request, 'create');
    }

    public function compraShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/compras');
        }
        $bundle = (new PurchaseOrderRepository())->findWithLines($id, $cid);
        if ($bundle === null) {
            Session::flash('error', 'Compra não encontrada.');
            redirect('/estoque/compras');
        }
        $squ = null;
        if (!empty($bundle['order']['supplier_quote_id'])) {
            $squ = (new SupplierQuoteRepository())->findWithLines((int) $bundle['order']['supplier_quote_id'], $cid);
        }

        return $this->view('estoque/compras/show', [
            'title' => 'Compra',
            'pageTitle' => (string) ($bundle['order']['document_number'] ?? 'Compra'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Compras', 'href' => '/estoque/compras'],
                ['label' => (string) ($bundle['order']['document_number'] ?? ''), 'href' => null],
            ],
            'bundle' => $bundle,
            'supplierQuote' => $squ,
        ]);
    }

    public function compraEditar(Request $request): string
    {
        return $this->compraForm($request, 'edit');
    }

    public function compraUpdate(Request $request): string
    {
        return $this->compraSave($request, 'edit');
    }

    public function compraFinalizar(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/estoque/compras');
        }
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/compras');
        }
        try {
            EstoqueOperacaoService::make()->finalizePurchase(
                $id,
                $cid,
                (int) auth_id(),
                $request->input('create_payable') === '1',
                trim((string) $request->input('payable_due_date', '')) ?: null
            );
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect('/estoque/compras/' . $id);
        }
        Session::flash('success', 'Compra finalizada e estoque atualizado.');

        redirect('/estoque/compras/' . $id);
    }

    private function compraForm(Request $request, string $mode): string
    {
        $cid = $this->requireCompany();
        $suppliers = (new SupplierRepository())->paginate($cid, '', null, 1, 3000)['rows'];
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 3000)['rows'];
        $stores = (new \App\Repositories\StoreRepository())->byCompanyId($cid);
        $quotes = (new SupplierQuoteRepository())->paginate($cid, '', 'approved', null, null, 1, 200)['rows'];
        $bundle = null;
        if ($mode === 'edit') {
            $id = $request->routeInt('id');
            if ($id === null) {
                redirect('/estoque/compras');
            }
            $bundle = (new PurchaseOrderRepository())->findWithLines($id, $cid);
            if ($bundle === null || (string) ($bundle['order']['status'] ?? '') !== 'open') {
                Session::flash('error', 'Compra não encontrada ou já finalizada.');
                redirect('/estoque/compras');
            }
        }

        return $this->view('estoque/compras/form', [
            'title' => $mode === 'edit' ? 'Editar compra' : 'Nova compra',
            'pageTitle' => 'Pedido de compra',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Compras', 'href' => '/estoque/compras'],
                ['label' => $mode === 'edit' ? 'Editar' : 'Nova', 'href' => null],
            ],
            'mode' => $mode,
            'suppliers' => $suppliers,
            'products' => $products,
            'stores' => $stores,
            'quotes' => $quotes,
            'bundle' => $bundle,
        ]);
    }

    private function compraSave(Request $request, string $mode): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/estoque/compras');
        }
        $lines = $this->parsePoLikeLines($request);
        if ($lines === []) {
            Session::flash('error', 'Inclua itens.');
            redirect('/estoque/compras/novo');
        }
        $tot = 0.0;
        foreach ($lines as $ln) {
            $tot += (float) $ln['line_total'];
        }
        $header = [
            'supplier_id' => (int) $request->input('supplier_id', 0),
            'document_number' => trim((string) $request->input('document_number', '')) ?: null,
            'status' => 'open',
            'total_amount' => $tot,
            'expected_at' => trim((string) $request->input('expected_at', '')) ?: null,
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'issued_at' => trim((string) $request->input('issued_at', '')) ?: null,
            'store_id' => (int) $request->input('store_id', 0) > 0 ? (int) $request->input('store_id') : null,
            'supplier_quote_id' => (int) $request->input('supplier_quote_id', 0) > 0 ? (int) $request->input('supplier_quote_id') : null,
        ];
        if ($header['supplier_id'] < 1) {
            Session::flash('error', 'Selecione o fornecedor.');
            redirect('/estoque/compras/novo');
        }
        $repo = new PurchaseOrderRepository();
        if ($mode === 'create') {
            $id = $repo->create($cid, $header, $lines, auth_id());
            Session::flash('success', 'Compra registrada em aberto.');

            redirect('/estoque/compras/' . $id);
        }
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/compras');
        }
        $repo->updateOpen($id, $cid, $header, $lines, auth_id());
        Session::flash('success', 'Compra atualizada.');

        redirect('/estoque/compras/' . $id);
    }

    public function trocasDevolucoes(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $result = (new StockReturnRepository())->paginate(
            $cid,
            trim((string) $request->input('q', '')),
            (string) $request->input('kind', 'all'),
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;

        return $this->view('estoque/trocas/index', [
            'title' => 'Trocas e devoluções',
            'pageTitle' => 'Trocas e devoluções',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Trocas e devoluções', 'href' => null],
            ],
            'rows' => $result['rows'],
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $result['total'],
            'basePath' => '/estoque/trocas-devolucoes',
            'queryParams' => array_filter([
                'q' => trim((string) $request->input('q', '')) !== '' ? trim((string) $request->input('q', '')) : null,
                'kind' => (string) $request->input('kind', 'all') !== 'all' ? (string) $request->input('kind', 'all') : null,
            ]),
            'search' => trim((string) $request->input('q', '')),
            'kindFilter' => (string) $request->input('kind', 'all'),
        ]);
    }

    public function trocasNovo(Request $request): string
    {
        $cid = $this->requireCompany();
        $stores = (new \App\Repositories\StoreRepository())->byCompanyId($cid);
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 2000)['rows'];
        $clients = (new \App\Repositories\ClientRepository())->allForCompany($cid);
        $suppliers = (new SupplierRepository())->paginate($cid, '', null, 1, 2000)['rows'];

        return $this->view('estoque/trocas/form', [
            'title' => 'Nova devolução',
            'pageTitle' => 'Registrar devolução / troca',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Trocas e devoluções', 'href' => '/estoque/trocas-devolucoes'],
                ['label' => 'Novo', 'href' => null],
            ],
            'stores' => $stores,
            'products' => $products,
            'clients' => $clients,
            'suppliers' => $suppliers,
        ]);
    }

    public function trocasStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/estoque/trocas-devolucoes/novo');
        }
        $kind = (string) $request->input('return_kind', 'sale_return');
        $storeId = (int) $request->input('store_id', 0);
        $pid = (int) $request->input('product_id', 0);
        $qty = (float) str_replace(',', '.', (string) $request->input('qty', '0'));
        if ($storeId < 1 || $pid < 1 || $qty <= 0) {
            Session::flash('error', 'Preencha loja, produto e quantidade.');
            redirect('/estoque/trocas-devolucoes/novo');
        }
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $id = (new StockReturnRepository())->insert($cid, [
                'store_id' => $storeId,
                'return_kind' => $kind,
                'client_id' => (int) $request->input('client_id', 0) > 0 ? (int) $request->input('client_id') : null,
                'supplier_id' => (int) $request->input('supplier_id', 0) > 0 ? (int) $request->input('supplier_id') : null,
                'sales_document_id' => (int) $request->input('sales_document_id', 0) > 0 ? (int) $request->input('sales_document_id') : null,
                'purchase_order_id' => (int) $request->input('purchase_order_id', 0) > 0 ? (int) $request->input('purchase_order_id') : null,
                'product_id' => $pid,
                'qty' => $qty,
                'reason' => trim((string) $request->input('reason', '')) ?: null,
                'notes' => trim((string) $request->input('notes', '')) ?: null,
                'status' => 'recorded',
                'created_by' => auth_id(),
            ]);
            EstoqueOperacaoService::make()->applyReturnStock($id, $cid, $kind, $storeId, $pid, $qty);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Session::flash('error', $e->getMessage());
            redirect('/estoque/trocas-devolucoes/novo');
        }
        Session::flash('success', 'Registro criado e estoque atualizado.');

        redirect('/estoque/trocas-devolucoes/' . $id);
    }

    public function trocaShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect('/estoque/trocas-devolucoes');
        }
        $row = (new StockReturnRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/estoque/trocas-devolucoes');
        }

        return $this->view('estoque/trocas/show', [
            'title' => 'Devolução #' . $id,
            'pageTitle' => 'Detalhe',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Trocas e devoluções', 'href' => '/estoque/trocas-devolucoes'],
                ['label' => '#' . $id, 'href' => null],
            ],
            'row' => $row,
        ]);
    }

    /**
     * @return list<array{product_id: int, qty: float, unit_cost: float, line_total: float}>  (cotacao)
     * @return list<array{product_id: int, qty: float, unit_price: float, line_discount: float, line_total: float}> (compra)
     */
    private function parsePoLikeLines(Request $request): array
    {
        $raw = $request->input('items', []);
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }
            $pid = (int) ($row['product_id'] ?? 0);
            if ($pid < 1) {
                continue;
            }
            $qty = (float) str_replace(',', '.', (string) ($row['qty'] ?? 0));
            $uc = (float) str_replace(',', '.', (string) ($row['unit_cost'] ?? $row['unit_price'] ?? 0));
            $ld = (float) str_replace(',', '.', (string) ($row['line_discount'] ?? 0));
            if ($qty <= 0) {
                continue;
            }
            $lt = max(0, $qty * $uc - $ld);
            $out[] = [
                'product_id' => $pid,
                'qty' => $qty,
                'unit_cost' => $uc,
                'unit_price' => $uc,
                'line_discount' => $ld,
                'line_total' => $lt,
            ];
        }

        return $out;
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
