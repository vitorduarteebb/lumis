<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ClientRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductStoreStockRepository;
use App\Repositories\SalesDocumentRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use App\Services\VendasService;
use Dompdf\Dompdf;
use Dompdf\Options;

final class VendasController extends Controller
{
    private const PER_PAGE = 15;

    /* ===================== Vendas > Produtos ===================== */

    public function produtos(Request $request): string
    {
        return $this->salesIndex($request, 'product', '/vendas/produtos');
    }

    public function produtosNovo(Request $request): string
    {
        return $this->salesForm($request, 'product', '/vendas/produtos', 'create');
    }

    public function produtosStore(Request $request): string
    {
        return $this->salesStore($request, 'product', '/vendas/produtos');
    }

    public function produtosShow(Request $request): string
    {
        return $this->salesShow($request, 'product', '/vendas/produtos');
    }

    public function produtosEdit(Request $request): string
    {
        return $this->salesForm($request, 'product', '/vendas/produtos', 'edit');
    }

    public function produtosUpdate(Request $request): string
    {
        return $this->salesUpdate($request, 'product', '/vendas/produtos');
    }

    public function produtosFinalizar(Request $request): string
    {
        return $this->salesFinalizePost($request, 'product', '/vendas/produtos');
    }

    public function produtosCancelar(Request $request): string
    {
        return $this->salesCancelPost($request, 'product', '/vendas/produtos');
    }

    public function produtosPdf(Request $request): void
    {
        $this->salesPdf($request, 'product', '/vendas/produtos');
    }

    /* ===================== Balcão ===================== */

    public function balcao(Request $request): string
    {
        $cid = $this->requireCompany();
        if ($request->method() === 'POST') {
            return $this->balcaoStore($request);
        }
        $stores = (new StoreRepository())->byCompanyId($cid);
        $products = (new ProductRepository())->paginate($cid, '', null, null, 1, 500)['rows'];
        $clients = (new ClientRepository())->allForCompany($cid);

        return $this->view('vendas/balcao/form', [
            'title' => 'Balcão',
            'pageTitle' => 'Vendas · Balcão',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Vendas', 'href' => null],
                ['label' => 'Balcão', 'href' => null],
            ],
            'stores' => $stores,
            'products' => $products,
            'clients' => $clients,
            'errors' => [],
            'old' => [],
        ]);
    }

    private function balcaoStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/vendas/balcao');
        }
        $repo = new SalesDocumentRepository();
        $storeId = (int) $request->input('store_id', 0);
        if ($storeId < 1) {
            $storeId = (int) ((new ProductStoreStockRepository())->defaultStoreId($cid) ?? 0);
        }
        $lines = $this->parseProductLines($request);
        if ($lines === []) {
            Session::flash('error', 'Inclua pelo menos um produto.');
            redirect('/vendas/balcao');
        }
        [$sub, $disc, $tot, $linesCalc] = $this->computeTotals($lines, (string) $request->input('discount_total', '0'));
        $header = [
            'client_id' => (int) $request->input('client_id', 0) > 0 ? (int) $request->input('client_id') : null,
            'store_id' => $storeId > 0 ? $storeId : null,
            'seller_user_id' => auth_id(),
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'subtotal_amount' => $sub,
            'discount_total' => $disc,
            'total_amount' => $tot,
            'payment_method_entry_id' => (int) $request->input('payment_method_entry_id', 0) ?: null,
            'payment_terms_entry_id' => (int) $request->input('payment_terms_entry_id', 0) ?: null,
            'sale_channel_entry_id' => null,
        ];
        $docId = $repo->createDraft($cid, 'balcao', $header, $linesCalc, auth_id());
        try {
            VendasService::make()->finalize(
                $docId,
                $cid,
                (int) auth_id(),
                $request->input('create_receivable') === '1',
                trim((string) $request->input('receivable_due_date', '')) ?: null
            );
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());

            return $this->balcao($request);
        }
        Session::flash('success', 'Venda de balcão registrada e finalizada.');

        redirect('/vendas/produtos/' . $docId);
    }

    /* ===================== Vendas > Serviços ===================== */

    public function servicos(Request $request): string
    {
        return $this->salesIndex($request, 'service', '/vendas/servicos');
    }

    public function servicosNovo(Request $request): string
    {
        return $this->salesForm($request, 'service', '/vendas/servicos', 'create');
    }

    public function servicosStore(Request $request): string
    {
        return $this->salesStore($request, 'service', '/vendas/servicos');
    }

    public function servicosShow(Request $request): string
    {
        return $this->salesShow($request, 'service', '/vendas/servicos');
    }

    public function servicosEdit(Request $request): string
    {
        return $this->salesForm($request, 'service', '/vendas/servicos', 'edit');
    }

    public function servicosUpdate(Request $request): string
    {
        return $this->salesUpdate($request, 'service', '/vendas/servicos');
    }

    public function servicosFinalizar(Request $request): string
    {
        return $this->salesFinalizePost($request, 'service', '/vendas/servicos');
    }

    public function servicosCancelar(Request $request): string
    {
        return $this->salesCancelPost($request, 'service', '/vendas/servicos');
    }

    public function servicosPdf(Request $request): void
    {
        $this->salesPdf($request, 'service', '/vendas/servicos');
    }

    /* ===================== Core ===================== */

    private function salesIndex(Request $request, string $kind, string $base): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $st = (string) $request->input('status', '');
        $df = trim((string) $request->input('date_from', '')) ?: null;
        $dt = trim((string) $request->input('date_to', '')) ?: null;
        $page = max(1, (int) $request->input('page', 1));
        $repo = new SalesDocumentRepository();
        $result = $repo->paginate($cid, $kind, $q, $st, $df, $dt, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;
        $label = $kind === 'service' ? 'Serviços' : 'Produtos';

        return $this->view('vendas/sales/index', [
            'title' => 'Vendas — ' . $label,
            'pageTitle' => 'Vendas · ' . $label,
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Vendas', 'href' => null],
                ['label' => $label, 'href' => null],
            ],
            'kind' => $kind,
            'basePath' => $base,
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'status' => $st,
            'dateFrom' => $df ?? '',
            'dateTo' => $dt ?? '',
            'queryParams' => array_filter([
                'q' => $q !== '' ? $q : null,
                'status' => $st !== '' && $st !== 'all' ? $st : null,
                'date_from' => $df ?: null,
                'date_to' => $dt ?: null,
            ]),
        ]);
    }

    private function salesForm(Request $request, string $kind, string $base, string $mode): string
    {
        $cid = $this->requireCompany();
        $clients = (new ClientRepository())->allForCompany($cid);
        $stores = (new StoreRepository())->byCompanyId($cid);
        $users = (new UserRepository())->listActiveForCompany($cid);
        $products = $kind !== 'service' ? (new ProductRepository())->paginate($cid, '', null, null, 1, 2000)['rows'] : [];
        $services = $kind === 'service' ? (new ServiceRepository())->paginate($cid, '', null, 1, 2000)['rows'] : [];
        $id = $request->routeInt('id');
        $bundle = null;
        if ($mode === 'edit' && $id !== null && $id > 0) {
            $bundle = (new SalesDocumentRepository())->findWithLines($id, $cid);
            if ($bundle === null || !$this->salesDocKindMatchesRoute($kind, (string) ($bundle['doc']['document_kind'] ?? ''))) {
                Session::flash('error', 'Venda não encontrada.');
                redirect($base);
            }
            if (($bundle['doc']['status'] ?? '') !== 'open') {
                Session::flash('error', 'Somente vendas em aberto podem ser editadas.');
                redirect($base . '/' . $id);
            }
        }

        return $this->view('vendas/sales/form', [
            'title' => $mode === 'edit' ? 'Editar venda' : 'Nova venda',
            'pageTitle' => $kind === 'service' ? 'Venda de serviços' : 'Venda de produtos',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Vendas', 'href' => null],
                ['label' => $kind === 'service' ? 'Serviços' : 'Produtos', 'href' => $base],
                ['label' => $mode === 'edit' ? 'Editar' : 'Novo', 'href' => null],
            ],
            'kind' => $kind,
            'basePath' => $base,
            'mode' => $mode,
            'clients' => $clients,
            'stores' => $stores,
            'users' => $users,
            'products' => $products,
            'services' => $services,
            'bundle' => $bundle,
            'errors' => [],
            'old' => [],
        ]);
    }

    private function salesStore(Request $request, string $kind, string $base): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($base . '/novo');
        }
        $lines = $kind === 'service' ? $this->parseServiceLines($request) : $this->parseProductLines($request);
        $errs = [];
        if ($lines === []) {
            $errs[] = 'Inclua pelo menos uma linha.';
        }
        [$sub, $disc, $tot, $linesCalc] = $this->computeTotals($lines, (string) $request->input('discount_total', '0'));
        if ($errs !== []) {
            Session::flash('error', implode(' ', $errs));
            redirect($base . '/novo');
        }
        $header = [
            'client_id' => (int) $request->input('client_id', 0) > 0 ? (int) $request->input('client_id') : null,
            'store_id' => (int) $request->input('store_id', 0) > 0 ? (int) $request->input('store_id') : null,
            'seller_user_id' => (int) $request->input('seller_user_id', 0) > 0 ? (int) $request->input('seller_user_id') : null,
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'subtotal_amount' => $sub,
            'discount_total' => $disc,
            'total_amount' => $tot,
            'payment_method_entry_id' => (int) $request->input('payment_method_entry_id', 0) ?: null,
            'payment_terms_entry_id' => (int) $request->input('payment_terms_entry_id', 0) ?: null,
            'sale_channel_entry_id' => (int) $request->input('sale_channel_entry_id', 0) ?: null,
        ];
        $docId = (new SalesDocumentRepository())->createDraft($cid, $kind, $header, $linesCalc, auth_id());
        Session::flash('success', 'Venda criada em aberto. Revise e finalize.');

        redirect($base . '/' . $docId);
    }

    private function salesShow(Request $request, string $kind, string $base): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($base);
        }
        $bundle = (new SalesDocumentRepository())->findWithLines($id, $cid);
        if ($bundle === null || !$this->salesDocKindMatchesRoute($kind, (string) ($bundle['doc']['document_kind'] ?? ''))) {
            Session::flash('error', 'Venda não encontrada.');
            redirect($base);
        }
        $stLabels = ['open' => 'Aberta', 'finalized' => 'Finalizada', 'cancelled' => 'Cancelada'];

        return $this->view('vendas/sales/show', [
            'title' => 'Venda ' . (string) ($bundle['doc']['document_number'] ?? ''),
            'pageTitle' => (string) ($bundle['doc']['document_number'] ?? 'Venda'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Vendas', 'href' => null],
                ['label' => $kind === 'service' ? 'Serviços' : 'Produtos', 'href' => $base],
                ['label' => (string) ($bundle['doc']['document_number'] ?? ''), 'href' => null],
            ],
            'kind' => $kind,
            'basePath' => $base,
            'bundle' => $bundle,
            'statusLabels' => $stLabels,
        ]);
    }

    private function salesUpdate(Request $request, string $kind, string $base): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($base);
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($base);
        }
        $lines = $kind === 'service' ? $this->parseServiceLines($request) : $this->parseProductLines($request);
        if ($lines === []) {
            Session::flash('error', 'Inclua pelo menos uma linha.');
            redirect($base . '/' . $id . '/editar');
        }
        [$sub, $disc, $tot, $linesCalc] = $this->computeTotals($lines, (string) $request->input('discount_total', '0'));
        $header = [
            'client_id' => (int) $request->input('client_id', 0) > 0 ? (int) $request->input('client_id') : null,
            'store_id' => (int) $request->input('store_id', 0) > 0 ? (int) $request->input('store_id') : null,
            'seller_user_id' => (int) $request->input('seller_user_id', 0) > 0 ? (int) $request->input('seller_user_id') : null,
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'subtotal_amount' => $sub,
            'discount_total' => $disc,
            'total_amount' => $tot,
            'payment_method_entry_id' => (int) $request->input('payment_method_entry_id', 0) ?: null,
            'payment_terms_entry_id' => (int) $request->input('payment_terms_entry_id', 0) ?: null,
            'sale_channel_entry_id' => (int) $request->input('sale_channel_entry_id', 0) ?: null,
        ];
        try {
            (new SalesDocumentRepository())->updateOpenDocument($id, $cid, $header, $linesCalc, auth_id());
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect($base . '/' . $id . '/editar');
        }
        Session::flash('success', 'Venda atualizada.');

        redirect($base . '/' . $id);
    }

    private function salesFinalizePost(Request $request, string $kind, string $base): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($base);
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($base);
        }
        try {
            VendasService::make()->finalize(
                $id,
                $cid,
                (int) auth_id(),
                $request->input('create_receivable') === '1',
                trim((string) $request->input('receivable_due_date', '')) ?: null
            );
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect($base . '/' . $id);
        }
        Session::flash('success', 'Venda finalizada.');

        redirect($base . '/' . $id);
    }

    private function salesCancelPost(Request $request, string $kind, string $base): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($base);
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($base);
        }
        try {
            VendasService::make()->cancel($id, $cid, (int) auth_id());
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect($base . '/' . $id);
        }
        Session::flash('success', 'Operação registrada.');

        redirect($base . '/' . $id);
    }

    private function salesPdf(Request $request, string $kind, string $base): void
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($base);
        }
        $bundle = (new SalesDocumentRepository())->findWithLines($id, $cid);
        if ($bundle === null || !$this->salesDocKindMatchesRoute($kind, (string) ($bundle['doc']['document_kind'] ?? ''))) {
            Session::flash('error', 'Venda não encontrada.');
            redirect($base);
        }
        $row = $bundle['doc'];
        $items = $bundle['lines'];
        ob_start();
        include base_path('app/Views/vendas/sales/pdf.php');
        $html = (string) ob_get_clean();
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $fn = 'venda-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($row['document_number'] ?? (string) $id)) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fn . '"');
        echo $dompdf->output();
        exit;
    }

    /**
     * @return list<array{product_id: int|null, service_id: int|null, description: string|null, qty: float, unit_price: float, line_discount: float, line_total: float}>
     */
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
            $lt = max(0, $qty * $up - $ld);
            $out[] = [
                'product_id' => $pid,
                'service_id' => null,
                'description' => isset($row['description']) ? trim((string) $row['description']) : null,
                'qty' => $qty,
                'unit_price' => $up,
                'line_discount' => max(0, $ld),
                'line_total' => $lt,
            ];
        }

        return $out;
    }

    /**
     * @return list<array{product_id: int|null, service_id: int|null, description: string|null, qty: float, unit_price: float, line_discount: float, line_total: float}>
     */
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
            $lt = max(0, $qty * $up - $ld);
            $out[] = [
                'product_id' => null,
                'service_id' => $sid,
                'description' => isset($row['description']) ? trim((string) $row['description']) : null,
                'qty' => $qty,
                'unit_price' => $up,
                'line_discount' => max(0, $ld),
                'line_total' => $lt,
            ];
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $lines
     * @return array{0: float, 1: float, 2: float, 3: list<array<string, mixed>>}
     */
    private function computeTotals(array $lines, string $discountRaw): array
    {
        $sub = 0.0;
        foreach ($lines as $ln) {
            $sub += (float) ($ln['line_total'] ?? 0);
        }
        $disc = (float) str_replace(',', '.', $discountRaw);
        if ($disc < 0) {
            $disc = 0;
        }
        $tot = max(0, $sub - $disc);

        return [$sub, $disc, $tot, $lines];
    }

    /**
     * Rotas de produtos incluem documentos kind product e balcao; serviços apenas service.
     */
    private function salesDocKindMatchesRoute(string $routeKind, string $docKind): bool
    {
        if ($routeKind === 'service') {
            return $docKind === 'service';
        }

        return in_array($docKind, ['product', 'balcao'], true);
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
