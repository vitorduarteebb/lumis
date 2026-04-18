<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ClientRepository;
use App\Repositories\FiscalDocumentRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\StoreRepository;
use App\Repositories\SupplierRepository;

final class NotasFiscaisController extends Controller
{
    private const PER_PAGE = 15;

    /** @return array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} */
    private function cfgProdutos(): array
    {
        return [
            'kind' => 'product_out',
            'base' => '/notas-fiscais/produtos',
            'perm' => 'notas_fiscais.produtos',
            'label' => 'Notas de produtos',
            'lineMode' => 'product',
            'bc' => 'Produtos',
        ];
    }

    /** @return array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} */
    private function cfgServicos(): array
    {
        return [
            'kind' => 'service',
            'base' => '/notas-fiscais/servicos',
            'perm' => 'notas_fiscais.servicos',
            'label' => 'Notas de serviços',
            'lineMode' => 'service',
            'bc' => 'Serviços',
        ];
    }

    /** @return array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} */
    private function cfgConsumidor(): array
    {
        return [
            'kind' => 'consumer',
            'base' => '/notas-fiscais/consumidor',
            'perm' => 'notas_fiscais.consumidor',
            'label' => 'Notas do consumidor',
            'lineMode' => 'product',
            'bc' => 'Consumidor',
        ];
    }

    /** @return array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} */
    private function cfgCompras(): array
    {
        return [
            'kind' => 'purchase_in',
            'base' => '/notas-fiscais/compras',
            'perm' => 'notas_fiscais.compras',
            'label' => 'Notas de compras',
            'lineMode' => 'product',
            'bc' => 'Compras',
        ];
    }

    public function produtos(Request $request): string
    {
        return $this->fiscalIndex($request, $this->cfgProdutos());
    }

    public function produtosNovo(Request $request): string
    {
        return $this->fiscalNovo($request, $this->cfgProdutos());
    }

    public function produtosStore(Request $request): string
    {
        return $this->fiscalStore($request, $this->cfgProdutos());
    }

    public function produtosShow(Request $request): string
    {
        return $this->fiscalShow($request, $this->cfgProdutos());
    }

    public function produtosEditar(Request $request): string
    {
        return $this->fiscalEditar($request, $this->cfgProdutos());
    }

    public function produtosUpdate(Request $request): string
    {
        return $this->fiscalUpdate($request, $this->cfgProdutos());
    }

    public function produtosCancelar(Request $request): string
    {
        return $this->fiscalCancelar($request, $this->cfgProdutos());
    }

    public function produtosStatus(Request $request): string
    {
        return $this->fiscalStatus($request, $this->cfgProdutos());
    }

    public function produtosDownload(Request $request): never
    {
        $this->fiscalDownload($request, $this->cfgProdutos());
    }

    public function servicos(Request $request): string
    {
        return $this->fiscalIndex($request, $this->cfgServicos());
    }

    public function servicosNovo(Request $request): string
    {
        return $this->fiscalNovo($request, $this->cfgServicos());
    }

    public function servicosStore(Request $request): string
    {
        return $this->fiscalStore($request, $this->cfgServicos());
    }

    public function servicosShow(Request $request): string
    {
        return $this->fiscalShow($request, $this->cfgServicos());
    }

    public function servicosEditar(Request $request): string
    {
        return $this->fiscalEditar($request, $this->cfgServicos());
    }

    public function servicosUpdate(Request $request): string
    {
        return $this->fiscalUpdate($request, $this->cfgServicos());
    }

    public function servicosCancelar(Request $request): string
    {
        return $this->fiscalCancelar($request, $this->cfgServicos());
    }

    public function servicosStatus(Request $request): string
    {
        return $this->fiscalStatus($request, $this->cfgServicos());
    }

    public function servicosDownload(Request $request): never
    {
        $this->fiscalDownload($request, $this->cfgServicos());
    }

    public function consumidor(Request $request): string
    {
        return $this->fiscalIndex($request, $this->cfgConsumidor());
    }

    public function consumidorNovo(Request $request): string
    {
        return $this->fiscalNovo($request, $this->cfgConsumidor());
    }

    public function consumidorStore(Request $request): string
    {
        return $this->fiscalStore($request, $this->cfgConsumidor());
    }

    public function consumidorShow(Request $request): string
    {
        return $this->fiscalShow($request, $this->cfgConsumidor());
    }

    public function consumidorEditar(Request $request): string
    {
        return $this->fiscalEditar($request, $this->cfgConsumidor());
    }

    public function consumidorUpdate(Request $request): string
    {
        return $this->fiscalUpdate($request, $this->cfgConsumidor());
    }

    public function consumidorCancelar(Request $request): string
    {
        return $this->fiscalCancelar($request, $this->cfgConsumidor());
    }

    public function consumidorStatus(Request $request): string
    {
        return $this->fiscalStatus($request, $this->cfgConsumidor());
    }

    public function consumidorDownload(Request $request): never
    {
        $this->fiscalDownload($request, $this->cfgConsumidor());
    }

    public function compras(Request $request): string
    {
        return $this->fiscalIndex($request, $this->cfgCompras());
    }

    public function comprasNovo(Request $request): string
    {
        return $this->fiscalNovo($request, $this->cfgCompras());
    }

    public function comprasStore(Request $request): string
    {
        return $this->fiscalStore($request, $this->cfgCompras());
    }

    public function comprasShow(Request $request): string
    {
        return $this->fiscalShow($request, $this->cfgCompras());
    }

    public function comprasEditar(Request $request): string
    {
        return $this->fiscalEditar($request, $this->cfgCompras());
    }

    public function comprasUpdate(Request $request): string
    {
        return $this->fiscalUpdate($request, $this->cfgCompras());
    }

    public function comprasCancelar(Request $request): string
    {
        return $this->fiscalCancelar($request, $this->cfgCompras());
    }

    public function comprasStatus(Request $request): string
    {
        return $this->fiscalStatus($request, $this->cfgCompras());
    }

    public function comprasDownload(Request $request): never
    {
        $this->fiscalDownload($request, $this->cfgCompras());
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalIndex(Request $request, array $cfg): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $repo = new FiscalDocumentRepository();
        $result = $repo->paginate(
            $cid,
            $cfg['kind'],
            trim((string) $request->input('q', '')),
            (string) $request->input('status', 'all'),
            ((int) $request->input('store_id', 0)) > 0 ? (int) $request->input('store_id') : null,
            trim((string) $request->input('date_from', '')) ?: null,
            trim((string) $request->input('date_to', '')) ?: null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;
        $stores = (new StoreRepository())->byCompanyId($cid);

        return $this->view('notas_fiscais/fiscal/index', [
            'title' => 'Notas fiscais — ' . $cfg['label'],
            'pageTitle' => $cfg['label'],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => $cfg['bc'], 'href' => null],
            ],
            'cfg' => $cfg,
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'basePath' => $cfg['base'],
            'queryParams' => array_filter([
                'q' => trim((string) $request->input('q', '')) !== '' ? trim((string) $request->input('q', '')) : null,
                'status' => (string) $request->input('status', 'all') !== 'all' ? (string) $request->input('status', 'all') : null,
                'store_id' => ((int) $request->input('store_id', 0)) > 0 ? (int) $request->input('store_id') : null,
                'date_from' => trim((string) $request->input('date_from', '')) ?: null,
                'date_to' => trim((string) $request->input('date_to', '')) ?: null,
            ]),
            'stores' => $stores,
            'search' => trim((string) $request->input('q', '')),
            'statusFilter' => (string) $request->input('status', 'all'),
            'filterStoreId' => ((int) $request->input('store_id', 0)) > 0 ? (int) $request->input('store_id') : 0,
            'dateFrom' => trim((string) $request->input('date_from', '')),
            'dateTo' => trim((string) $request->input('date_to', '')),
        ]);
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalNovo(Request $request, array $cfg): string
    {
        $cid = $this->requireCompany();
        $clients = (new ClientRepository())->allForCompany($cid);
        $suppliers = (new SupplierRepository())->paginate($cid, '', null, 1, 3000)['rows'];
        $stores = (new StoreRepository())->byCompanyId($cid);
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 3000)['rows'];
        $services = (new ServiceRepository())->paginate($cid, '', null, 1, 3000)['rows'];
        $purchaseOrders = [];
        if ($cfg['kind'] === 'purchase_in' && $this->tableExistsSafe('purchase_orders')) {
            $purchaseOrders = (new PurchaseOrderRepository())->paginate($cid, '', 'all', null, null, 1, 200)['rows'];
        }

        return $this->view('notas_fiscais/fiscal/form', [
            'title' => 'Nova — ' . $cfg['label'],
            'pageTitle' => 'Nova nota',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => $cfg['bc'], 'href' => $cfg['base']],
                ['label' => 'Novo', 'href' => null],
            ],
            'cfg' => $cfg,
            'mode' => 'create',
            'bundle' => null,
            'clients' => $clients,
            'suppliers' => $suppliers,
            'stores' => $stores,
            'products' => $products,
            'services' => $services,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalStore(Request $request, array $cfg): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($cfg['base'] . '/novo');
        }
        $lines = $cfg['lineMode'] === 'service' ? $this->parseServiceLines($request) : $this->parseProductLines($request);
        if ($lines === []) {
            Session::flash('error', 'Inclua pelo menos uma linha.');
            redirect($cfg['base'] . '/novo');
        }
        [$sub, $disc, $tot] = $this->computeDocTotals($lines, (string) $request->input('discount_total', '0'));
        $header = $this->buildHeaderFromRequest($request, $cfg['kind'], $sub, $disc, $tot);
        if ($cfg['kind'] === 'purchase_in' && ((int) ($header['supplier_id'] ?? 0)) < 1) {
            Session::flash('error', 'Selecione o fornecedor.');
            redirect($cfg['base'] . '/novo');
        }
        $repo = new FiscalDocumentRepository();
        try {
            $id = $repo->create($cid, $cfg['kind'], $header, $lines, auth_id());
            $this->processUploads($cid, $id, $cfg['base'], $request);
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect($cfg['base'] . '/novo');
        }
        Session::flash('success', 'Nota registrada.');

        redirect($cfg['base'] . '/' . $id);
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalShow(Request $request, array $cfg): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($cfg['base']);
        }
        $bundle = (new FiscalDocumentRepository())->findWithLines($id, $cid);
        if ($bundle === null || (string) ($bundle['doc']['document_kind'] ?? '') !== $cfg['kind']) {
            Session::flash('error', 'Nota não encontrada.');
            redirect($cfg['base']);
        }

        return $this->view('notas_fiscais/fiscal/show', [
            'title' => 'Nota fiscal',
            'pageTitle' => (string) ($bundle['doc']['document_number'] ?? 'Nota'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => $cfg['bc'], 'href' => $cfg['base']],
                ['label' => (string) ($bundle['doc']['document_number'] ?? '#' . $id), 'href' => null],
            ],
            'cfg' => $cfg,
            'bundle' => $bundle,
        ]);
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalEditar(Request $request, array $cfg): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($cfg['base']);
        }
        $bundle = (new FiscalDocumentRepository())->findWithLines($id, $cid);
        if ($bundle === null || (string) ($bundle['doc']['document_kind'] ?? '') !== $cfg['kind']) {
            Session::flash('error', 'Nota não encontrada.');
            redirect($cfg['base']);
        }
        $st = (string) ($bundle['doc']['status'] ?? '');
        if (!in_array($st, ['draft', 'error'], true)) {
            Session::flash('error', 'Somente notas em digitada ou erro podem ser editadas.');
            redirect($cfg['base'] . '/' . $id);
        }
        $clients = (new ClientRepository())->allForCompany($cid);
        $suppliers = (new SupplierRepository())->paginate($cid, '', null, 1, 3000)['rows'];
        $stores = (new StoreRepository())->byCompanyId($cid);
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 3000)['rows'];
        $services = (new ServiceRepository())->paginate($cid, '', null, 1, 3000)['rows'];
        $purchaseOrders = [];
        if ($this->tableExistsSafe('purchase_orders')) {
            $purchaseOrders = (new PurchaseOrderRepository())->paginate($cid, '', 'all', null, null, 1, 200)['rows'];
        }

        return $this->view('notas_fiscais/fiscal/form', [
            'title' => 'Editar nota',
            'pageTitle' => 'Editar',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => $cfg['bc'], 'href' => $cfg['base']],
                ['label' => 'Editar', 'href' => null],
            ],
            'cfg' => $cfg,
            'mode' => 'edit',
            'bundle' => $bundle,
            'clients' => $clients,
            'suppliers' => $suppliers,
            'stores' => $stores,
            'products' => $products,
            'services' => $services,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    private function tableExistsSafe(string $t): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $t)) {
            return false;
        }
        $c = new class extends \App\Repositories\BaseRepository {
            public function exists(string $table): bool
            {
                return $this->tableExists($table);
            }
        };

        return $c->exists($t);
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalUpdate(Request $request, array $cfg): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($cfg['base']);
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($cfg['base']);
        }
        $lines = $cfg['lineMode'] === 'service' ? $this->parseServiceLines($request) : $this->parseProductLines($request);
        if ($lines === []) {
            Session::flash('error', 'Inclua linhas.');
            redirect($cfg['base'] . '/' . $id . '/editar');
        }
        [$sub, $disc, $tot] = $this->computeDocTotals($lines, (string) $request->input('discount_total', '0'));
        $header = $this->buildHeaderFromRequest($request, $cfg['kind'], $sub, $disc, $tot);
        $repo = new FiscalDocumentRepository();
        try {
            $repo->updateOpen($id, $cid, $header, $lines, auth_id());
            $this->processUploads($cid, $id, $cfg['base'], $request);
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect($cfg['base'] . '/' . $id . '/editar');
        }
        Session::flash('success', 'Nota atualizada.');

        redirect($cfg['base'] . '/' . $id);
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalCancelar(Request $request, array $cfg): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($cfg['base']);
        }
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect($cfg['base']);
        }
        (new FiscalDocumentRepository())->setStatus($id, $cid, 'cancelled', auth_id());
        Session::flash('success', 'Nota cancelada.');

        redirect($cfg['base'] . '/' . $id);
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalStatus(Request $request, array $cfg): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($cfg['base']);
        }
        $id = $request->routeInt('id');
        if ($id === null) {
            redirect($cfg['base']);
        }
        $st = (string) $request->input('new_status', 'draft');
        $allowed = ['draft', 'issued', 'cancelled', 'voided', 'error'];
        if (!in_array($st, $allowed, true)) {
            Session::flash('error', 'Status inválido.');
            redirect($cfg['base'] . '/' . $id);
        }
        (new FiscalDocumentRepository())->setStatus($id, $cid, $st, auth_id());
        Session::flash('success', 'Status atualizado.');

        redirect($cfg['base'] . '/' . $id);
    }

    /** @param array{kind: string, base: string, perm: string, label: string, lineMode: string, bc: string} $cfg */
    private function fiscalDownload(Request $request, array $cfg): never
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        $tipo = (string) ($request->route('tipo') ?? '');
        if ($id === null || !in_array($tipo, ['xml', 'pdf'], true)) {
            redirect($cfg['base']);
        }
        $bundle = (new FiscalDocumentRepository())->findWithLines($id, $cid);
        if ($bundle === null || (string) ($bundle['doc']['document_kind'] ?? '') !== $cfg['kind']) {
            Session::flash('error', 'Arquivo não encontrado.');
            redirect($cfg['base']);
        }
        $doc = $bundle['doc'];
        $rel = $tipo === 'xml' ? (string) ($doc['xml_path'] ?? '') : (string) ($doc['pdf_path'] ?? '');
        if ($rel === '') {
            Session::flash('error', 'Anexo não cadastrado.');
            redirect($cfg['base'] . '/' . $id);
        }
        $abs = base_path($rel);
        if (!is_file($abs)) {
            Session::flash('error', 'Arquivo não encontrado no servidor.');
            redirect($cfg['base'] . '/' . $id);
        }
        $mime = $tipo === 'xml' ? 'application/xml' : 'application/pdf';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($abs) . '"');
        readfile($abs);
        exit;
    }

    /** @return list<array{product_id: int|null, service_id: int|null, description?: string|null, qty: float, unit_price: float, line_discount: float, line_total: float}> */
    private function parseProductLines(Request $request): array
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
            $up = (float) str_replace(',', '.', (string) ($row['unit_price'] ?? 0));
            $ld = (float) str_replace(',', '.', (string) ($row['line_discount'] ?? 0));
            if ($qty <= 0) {
                continue;
            }
            $out[] = [
                'product_id' => $pid,
                'service_id' => null,
                'description' => isset($row['description']) ? trim((string) $row['description']) : null,
                'qty' => $qty,
                'unit_price' => $up,
                'line_discount' => max(0, $ld),
                'line_total' => max(0, $qty * $up - $ld),
            ];
        }

        return $out;
    }

    /** @return list<array{product_id: int|null, service_id: int|null, description?: string|null, qty: float, unit_price: float, line_discount: float, line_total: float}> */
    private function parseServiceLines(Request $request): array
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
            $sid = (int) ($row['service_id'] ?? 0);
            if ($sid < 1) {
                continue;
            }
            $qty = (float) str_replace(',', '.', (string) ($row['qty'] ?? 0));
            $up = (float) str_replace(',', '.', (string) ($row['unit_price'] ?? 0));
            $ld = (float) str_replace(',', '.', (string) ($row['line_discount'] ?? 0));
            if ($qty <= 0) {
                continue;
            }
            $out[] = [
                'product_id' => null,
                'service_id' => $sid,
                'description' => isset($row['description']) ? trim((string) $row['description']) : null,
                'qty' => $qty,
                'unit_price' => $up,
                'line_discount' => max(0, $ld),
                'line_total' => max(0, $qty * $up - $ld),
            ];
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $lines
     * @return array{0: float, 1: float, 2: float}
     */
    private function computeDocTotals(array $lines, string $discountRaw): array
    {
        $sub = 0.0;
        foreach ($lines as $ln) {
            $sub += (float) ($ln['line_total'] ?? 0);
        }
        $disc = (float) str_replace(',', '.', $discountRaw);
        if ($disc < 0) {
            $disc = 0;
        }

        return [$sub, $disc, max(0, $sub - $disc)];
    }

    /** @return array<string, mixed> */
    private function buildHeaderFromRequest(Request $request, string $kind, float $sub, float $disc, float $tot): array
    {
        $issued = trim((string) $request->input('issued_at', ''));
        $issuedAt = $issued !== '' ? $issued : null;

        return [
            'store_id' => (int) $request->input('store_id', 0) > 0 ? (int) $request->input('store_id') : null,
            'client_id' => (int) $request->input('client_id', 0) > 0 ? (int) $request->input('client_id') : null,
            'supplier_id' => (int) $request->input('supplier_id', 0) > 0 ? (int) $request->input('supplier_id') : null,
            'document_number' => trim((string) $request->input('document_number', '')) ?: null,
            'series' => trim((string) $request->input('series', '')) ?: null,
            'access_key' => trim((string) $request->input('access_key', '')) ?: null,
            'issued_at' => $issuedAt !== null ? str_replace('T', ' ', $issuedAt) : null,
            'status' => (string) $request->input('status', 'draft'),
            'subtotal_amount' => $sub,
            'discount_total' => $disc,
            'total_amount' => $tot,
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'purchase_order_id' => (int) $request->input('purchase_order_id', 0) > 0 ? (int) $request->input('purchase_order_id') : null,
            'nature_entry_id' => (int) $request->input('nature_entry_id', 0) > 0 ? (int) $request->input('nature_entry_id') : null,
            'cfop_entry_id' => (int) $request->input('cfop_entry_id', 0) > 0 ? (int) $request->input('cfop_entry_id') : null,
            'model_entry_id' => (int) $request->input('model_entry_id', 0) > 0 ? (int) $request->input('model_entry_id') : null,
            'series_entry_id' => (int) $request->input('series_entry_id', 0) > 0 ? (int) $request->input('series_entry_id') : null,
        ];
    }

    private function processUploads(int $companyId, int $docId, string $base, Request $request): void
    {
        $repo = new FiscalDocumentRepository();
        $bundle = $repo->findWithLines($docId, $companyId);
        if ($bundle === null) {
            return;
        }
        $xmlPath = (string) ($bundle['doc']['xml_path'] ?? '');
        $pdfPath = (string) ($bundle['doc']['pdf_path'] ?? '');
        $dir = base_path('storage/uploads/fiscal/' . $companyId . '/' . $docId);
        if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) {
            return;
        }
        if (isset($_FILES['file_xml']) && is_array($_FILES['file_xml']) && (int) ($_FILES['file_xml']['error'] ?? 0) === UPLOAD_ERR_OK) {
            $tmp = (string) ($_FILES['file_xml']['tmp_name'] ?? '');
            $safe = 'nota_' . $docId . '_upload.xml';
            if ($tmp !== '' && move_uploaded_file($tmp, $dir . '/' . $safe)) {
                $xmlPath = 'storage/uploads/fiscal/' . $companyId . '/' . $docId . '/' . $safe;
            }
        }
        if (isset($_FILES['file_pdf']) && is_array($_FILES['file_pdf']) && (int) ($_FILES['file_pdf']['error'] ?? 0) === UPLOAD_ERR_OK) {
            $tmp = (string) ($_FILES['file_pdf']['tmp_name'] ?? '');
            $safe = 'nota_' . $docId . '_upload.pdf';
            if ($tmp !== '' && move_uploaded_file($tmp, $dir . '/' . $safe)) {
                $pdfPath = 'storage/uploads/fiscal/' . $companyId . '/' . $docId . '/' . $safe;
            }
        }
        if ($xmlPath !== (string) ($bundle['doc']['xml_path'] ?? '') || $pdfPath !== (string) ($bundle['doc']['pdf_path'] ?? '')) {
            $repo->updatePaths($docId, $companyId, $xmlPath ?: null, $pdfPath ?: null, auth_id());
        }
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
