<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ClientRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuoteRepository;
use App\Repositories\SalesDocumentRepository;
use App\Repositories\ServiceOrderRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

final class OrcamentosController extends Controller
{
    private const PER_PAGE = 15;

    /** @var list<string> */
    private const STATUSES = ['open', 'approved', 'rejected', 'cancelled', 'converted'];

    public function produtos(Request $request): string
    {
        return $this->quotesIndex($request, 'product');
    }

    public function produtosNovo(Request $request): string
    {
        return $this->quotesCreate($request, 'product');
    }

    public function produtosStore(Request $request): string
    {
        return $this->quotesStore($request, 'product');
    }

    public function produtosShow(Request $request): string
    {
        return $this->quotesShow($request, 'product');
    }

    public function produtosEdit(Request $request): string
    {
        return $this->quotesEdit($request, 'product');
    }

    public function produtosUpdate(Request $request): string
    {
        return $this->quotesUpdate($request, 'product');
    }

    public function produtosDestroy(Request $request): string
    {
        return $this->quotesDestroy($request, 'product');
    }

    public function produtosDuplicate(Request $request): string
    {
        return $this->quotesDuplicate($request, 'product');
    }

    public function produtosPdf(Request $request): void
    {
        $this->quotesPdf($request, 'product');
    }

    public function produtosConverterVenda(Request $request): string
    {
        return $this->quotesConvertSale($request, 'product');
    }

    public function servicos(Request $request): string
    {
        return $this->quotesIndex($request, 'service');
    }

    public function servicosNovo(Request $request): string
    {
        return $this->quotesCreate($request, 'service');
    }

    public function servicosStore(Request $request): string
    {
        return $this->quotesStore($request, 'service');
    }

    public function servicosShow(Request $request): string
    {
        return $this->quotesShow($request, 'service');
    }

    public function servicosEdit(Request $request): string
    {
        return $this->quotesEdit($request, 'service');
    }

    public function servicosUpdate(Request $request): string
    {
        return $this->quotesUpdate($request, 'service');
    }

    public function servicosDestroy(Request $request): string
    {
        return $this->quotesDestroy($request, 'service');
    }

    public function servicosDuplicate(Request $request): string
    {
        return $this->quotesDuplicate($request, 'service');
    }

    public function servicosPdf(Request $request): void
    {
        $this->quotesPdf($request, 'service');
    }

    public function servicosConverterOs(Request $request): string
    {
        return $this->quotesConvertToServiceOrder($request);
    }

    private function basePath(string $kind): string
    {
        return $kind === 'product' ? '/orcamentos/produtos' : '/orcamentos/servicos';
    }

    private function quotesIndex(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $df = trim((string) $request->input('from', ''));
        $dt = trim((string) $request->input('to', ''));
        $page = max(1, (int) $request->input('page', 1));
        $repo = new QuoteRepository();
        $result = $repo->paginate($cid, $kind, $q, $status, $df !== '' ? $df : null, $dt !== '' ? $dt : null, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;
        $bp = $this->basePath($kind);
        $label = $kind === 'product' ? 'Produtos' : 'Serviços';

        return $this->view('orcamentos/quotes/index', [
            'title' => 'Orçamentos — ' . $label,
            'pageTitle' => 'Orçamentos · ' . $label,
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => $label, 'href' => null],
            ],
            'quoteKind' => $kind,
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'statusFilter' => $status,
            'dateFrom' => $df,
            'dateTo' => $dt,
            'basePath' => $bp,
            'statuses' => self::STATUSES,
            'queryParams' => array_filter([
                'q' => $q !== '' ? $q : null,
                'status' => $status !== '' ? $status : null,
                'from' => $df !== '' ? $df : null,
                'to' => $dt !== '' ? $dt : null,
            ]),
        ]);
    }

    private function quotesCreate(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        $clients = (new ClientRepository())->listForSelect($cid);
        $products = $kind === 'product' ? (new ProductRepository())->listForSelect($cid) : [];
        $services = $kind === 'service' ? (new \App\Repositories\ServiceRepository())->listForSelect($cid) : [];

        return $this->view('orcamentos/quotes/form', [
            'title' => $kind === 'product' ? 'Novo orçamento de produtos' : 'Novo orçamento de serviços',
            'pageTitle' => 'Novo orçamento',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => $kind === 'product' ? 'Produtos' : 'Serviços', 'href' => $this->basePath($kind)],
                ['label' => 'Novo', 'href' => null],
            ],
            'quoteKind' => $kind,
            'mode' => 'create',
            'quote' => null,
            'items' => [],
            'clients' => $clients,
            'products' => $products,
            'services' => $services,
            'statuses' => self::STATUSES,
            'errors' => [],
            'old' => [],
        ]);
    }

    private function quotesStore(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($this->basePath($kind) . '/novo');
        }
        $lines = $this->parseLines($request, $kind);
        $clientId = (int) $request->input('client_id', 0);
        $status = trim((string) $request->input('status', 'open'));
        if (!in_array($status, self::STATUSES, true)) {
            $status = 'open';
        }
        $discount = (float) str_replace(',', '.', (string) $request->input('discount_total', '0'));
        $validUntil = trim((string) $request->input('valid_until', ''));
        $issuedAt = trim((string) $request->input('issued_at', ''));
        $notes = trim((string) $request->input('notes', ''));
        $errors = [];
        if ($clientId < 1) {
            $errors['client_id'] = 'Selecione o cliente.';
        }
        if ($errors !== []) {
            return $this->refillFormError($kind, 'create', null, $lines, $errors, $request);
        }
        $qRepo = new QuoteRepository();
        $uid = auth_id();
        $id = $qRepo->insert($cid, [
            'quote_kind' => $kind,
            'client_id' => $clientId,
            'status' => $status,
            'discount_total' => max(0, $discount),
            'valid_until' => $validUntil !== '' ? $validUntil : null,
            'issued_at' => $issuedAt !== '' ? $issuedAt : null,
            'notes' => $notes !== '' ? $notes : null,
            'created_by' => $uid,
        ]);
        $qRepo->replaceItems($id, $kind, $lines);
        $qRepo->recalcTotals($id);
        Session::flash('success', 'Orçamento criado.');
        redirect($this->basePath($kind) . '/' . $id);
    }

    private function quotesShow(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($this->basePath($kind));
        }
        $qRepo = new QuoteRepository();
        $row = $qRepo->findByIdForCompany($id, $cid);
        if ($row === null || (string) $row['quote_kind'] !== $kind) {
            Session::flash('error', 'Orçamento não encontrado.');
            redirect($this->basePath($kind));
        }
        $items = $qRepo->getItems($id);
        $label = $kind === 'product' ? 'Produtos' : 'Serviços';

        return $this->view('orcamentos/quotes/show', [
            'title' => 'Orçamento ' . ((string) ($row['quote_number'] ?? '')),
            'pageTitle' => (string) ($row['quote_number'] ?? 'Orçamento'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => $label, 'href' => $this->basePath($kind)],
                ['label' => (string) ($row['quote_number'] ?? ''), 'href' => null],
            ],
            'quoteKind' => $kind,
            'quote' => $row,
            'items' => $items,
            'statuses' => self::STATUSES,
            'basePath' => $this->basePath($kind),
        ]);
    }

    private function quotesEdit(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($this->basePath($kind));
        }
        $qRepo = new QuoteRepository();
        $row = $qRepo->findByIdForCompany($id, $cid);
        if ($row === null || (string) $row['quote_kind'] !== $kind) {
            Session::flash('error', 'Orçamento não encontrado.');
            redirect($this->basePath($kind));
        }
        if (in_array((string) $row['status'], ['converted', 'cancelled'], true)) {
            Session::flash('error', 'Este orçamento não pode ser editado.');
            redirect($this->basePath($kind) . '/' . $id);
        }
        $items = $qRepo->getItems($id);
        $clients = (new ClientRepository())->listForSelect($cid);
        $products = $kind === 'product' ? (new ProductRepository())->listForSelect($cid) : [];
        $services = $kind === 'service' ? (new \App\Repositories\ServiceRepository())->listForSelect($cid) : [];

        return $this->view('orcamentos/quotes/form', [
            'title' => 'Editar orçamento',
            'pageTitle' => 'Editar',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => $kind === 'product' ? 'Produtos' : 'Serviços', 'href' => $this->basePath($kind)],
                ['label' => 'Editar', 'href' => null],
            ],
            'quoteKind' => $kind,
            'mode' => 'edit',
            'quote' => $row,
            'items' => $items,
            'clients' => $clients,
            'products' => $products,
            'services' => $services,
            'statuses' => self::STATUSES,
            'errors' => [],
            'old' => [],
        ]);
    }

    private function quotesUpdate(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($this->basePath($kind));
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($this->basePath($kind));
        }
        $qRepo = new QuoteRepository();
        $row = $qRepo->findByIdForCompany($id, $cid);
        if ($row === null || (string) $row['quote_kind'] !== $kind) {
            Session::flash('error', 'Orçamento não encontrado.');
            redirect($this->basePath($kind));
        }
        if (in_array((string) $row['status'], ['converted', 'cancelled'], true)) {
            Session::flash('error', 'Alteração não permitida.');
            redirect($this->basePath($kind) . '/' . $id);
        }
        $lines = $this->parseLines($request, $kind);
        $clientId = (int) $request->input('client_id', 0);
        $status = trim((string) $request->input('status', 'open'));
        if (!in_array($status, self::STATUSES, true)) {
            $status = 'open';
        }
        $discount = (float) str_replace(',', '.', (string) $request->input('discount_total', '0'));
        $validUntil = trim((string) $request->input('valid_until', ''));
        $issuedAt = trim((string) $request->input('issued_at', ''));
        $notes = trim((string) $request->input('notes', ''));
        $errors = [];
        if ($clientId < 1) {
            $errors['client_id'] = 'Selecione o cliente.';
        }
        if ($errors !== []) {
            return $this->refillFormError($kind, 'edit', $row, $lines, $errors, $request);
        }
        $qRepo->update($id, $cid, [
            'client_id' => $clientId,
            'status' => $status,
            'discount_total' => max(0, $discount),
            'valid_until' => $validUntil !== '' ? $validUntil : null,
            'issued_at' => $issuedAt !== '' ? $issuedAt : null,
            'notes' => $notes !== '' ? $notes : null,
        ]);
        $qRepo->replaceItems($id, $kind, $lines);
        $qRepo->recalcTotals($id);
        Session::flash('success', 'Orçamento atualizado.');
        redirect($this->basePath($kind) . '/' . $id);
    }

    private function quotesDestroy(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($this->basePath($kind));
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($this->basePath($kind));
        }
        $qRepo = new QuoteRepository();
        $row = $qRepo->findByIdForCompany($id, $cid);
        if ($row === null || (string) $row['quote_kind'] !== $kind) {
            Session::flash('error', 'Orçamento não encontrado.');
            redirect($this->basePath($kind));
        }
        $qRepo->softDelete($id, $cid);
        Session::flash('success', 'Orçamento cancelado/excluído.');
        redirect($this->basePath($kind));
    }

    private function quotesDuplicate(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect($this->basePath($kind));
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($this->basePath($kind));
        }
        $qRepo = new QuoteRepository();
        $newId = $qRepo->duplicate($id, $cid, auth_id());
        if ($newId === null) {
            Session::flash('error', 'Não foi possível duplicar.');
            redirect($this->basePath($kind));
        }
        Session::flash('success', 'Orçamento duplicado.');
        redirect($this->basePath($kind) . '/' . $newId . '/editar');
    }

    private function quotesPdf(Request $request, string $kind): void
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($this->basePath($kind));
        }
        $qRepo = new QuoteRepository();
        $row = $qRepo->findByIdForCompany($id, $cid);
        if ($row === null || (string) $row['quote_kind'] !== $kind) {
            Session::flash('error', 'Orçamento não encontrado.');
            redirect($this->basePath($kind));
        }
        $items = $qRepo->getItems($id);
        ob_start();
        include base_path('app/Views/orcamentos/quotes/pdf.php');
        $html = (string) ob_get_clean();
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $fn = 'orcamento-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($row['quote_number'] ?? (string) $id)) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fn . '"');
        echo $dompdf->output();
        exit;
    }

    private function quotesConvertSale(Request $request, string $kind): string
    {
        $cid = $this->requireCompany();
        if ($kind !== 'product') {
            redirect('/orcamentos/servicos');
        }
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/orcamentos/produtos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/orcamentos/produtos');
        }
        $qRepo = new QuoteRepository();
        $row = $qRepo->findByIdForCompany($id, $cid);
        if ($row === null || (string) $row['quote_kind'] !== 'product') {
            Session::flash('error', 'Orçamento inválido.');
            redirect('/orcamentos/produtos');
        }
        if (in_array((string) $row['status'], ['converted', 'cancelled'], true)) {
            Session::flash('error', 'Conversão não permitida para este status.');
            redirect('/orcamentos/produtos/' . $id);
        }
        $items = $qRepo->getItems($id);
        $linesForDoc = [];
        foreach ($items as $it) {
            $pid = $it['product_id'] !== null ? (int) $it['product_id'] : 0;
            if ($pid < 1) {
                continue;
            }
            $qty = (float) $it['qty'];
            $linesForDoc[] = [
                'product_id' => $pid,
                'service_id' => null,
                'description' => $it['description'] ?? null,
                'qty' => $qty,
                'unit_price' => (float) $it['unit_price'],
                'line_total' => (float) $it['line_total'],
            ];
        }
        if ($linesForDoc === []) {
            Session::flash('error', 'Inclua pelo menos um item de produto.');
            redirect('/orcamentos/produtos/' . $id . '/editar');
        }
        $total = (float) ($row['total_amount'] ?? 0);
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $docId = (new SalesDocumentRepository())->createFromQuote(
                $cid,
                $row['client_id'] !== null ? (int) $row['client_id'] : null,
                $total,
                $linesForDoc,
                auth_id()
            );
            $ref = 'ORC-' . $id . '/VD-' . $docId;
            $prodRepo = new ProductRepository();
            foreach ($items as $it) {
                $pid = $it['product_id'] !== null ? (int) $it['product_id'] : 0;
                if ($pid < 1) {
                    continue;
                }
                $qty = (float) $it['qty'];
                if (!$prodRepo->applyStockOutNoTx($cid, $pid, $qty, $ref, auth_id())) {
                    throw new \RuntimeException('Estoque insuficiente para um ou mais produtos.');
                }
            }
            $qRepo->setConversion($id, $cid, $docId);
            $pdo->commit();
            Session::flash('success', 'Venda registrada (documento VD-' . $docId . ').');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            Session::flash('error', $e->getMessage());
            redirect('/orcamentos/produtos/' . $id);
        }
        redirect('/orcamentos/produtos/' . $id);
    }

    private function quotesConvertToServiceOrder(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/orcamentos/servicos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/orcamentos/servicos');
        }
        $qRepo = new QuoteRepository();
        $row = $qRepo->findByIdForCompany($id, $cid);
        if ($row === null || (string) $row['quote_kind'] !== 'service') {
            Session::flash('error', 'Orçamento inválido.');
            redirect('/orcamentos/servicos');
        }
        if (in_array((string) $row['status'], ['converted', 'cancelled'], true)) {
            Session::flash('error', 'Conversão não permitida.');
            redirect('/orcamentos/servicos/' . $id);
        }
        $items = $qRepo->getItems($id);
        $soRepo = new ServiceOrderRepository();
        $lines = [];
        foreach ($items as $it) {
            $sid = $it['service_id'] !== null ? (int) $it['service_id'] : 0;
            if ($sid < 1) {
                continue;
            }
            $lines[] = [
                'service_id' => $sid,
                'product_id' => null,
                'description' => $it['description'] ?? null,
                'qty' => (float) $it['qty'],
                'unit_price' => (float) $it['unit_price'],
                'line_discount' => (float) ($it['line_discount'] ?? 0),
            ];
        }
        if ($lines === []) {
            Session::flash('error', 'Inclua pelo menos um serviço no orçamento.');
            redirect('/orcamentos/servicos/' . $id . '/editar');
        }
        $soId = $soRepo->insert($cid, [
            'client_id' => $row['client_id'] !== null ? (int) $row['client_id'] : null,
            'quote_id' => $id,
            'status' => 'open',
            'priority' => 'normal',
            'description' => $row['notes'] ?? null,
            'internal_notes' => null,
            'customer_notes' => null,
            'assigned_user_id' => null,
            'opened_at' => date('Y-m-d H:i:s'),
            'expected_at' => null,
            'completed_at' => null,
            'os_type' => null,
        ]);
        $soRepo->replaceItems($soId, $lines);
        $qRepo->markStatus($id, $cid, 'converted');
        Session::flash('success', 'Ordem de serviço criada.');
        redirect('/ordens-servico/' . $soId);

        return '';
    }

    /**
     * @param list<array<string, mixed>> $lines
     * @param array<string, string> $errors
     */
    private function refillFormError(string $kind, string $mode, ?array $quote, array $lines, array $errors, Request $request): string
    {
        $cid = $this->requireCompany();
        $clients = (new ClientRepository())->listForSelect($cid);
        $products = $kind === 'product' ? (new ProductRepository())->listForSelect($cid) : [];
        $services = $kind === 'service' ? (new \App\Repositories\ServiceRepository())->listForSelect($cid) : [];

        return $this->view('orcamentos/quotes/form', [
            'title' => 'Corrigir orçamento',
            'pageTitle' => 'Orçamento',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => $kind === 'product' ? 'Produtos' : 'Serviços', 'href' => $this->basePath($kind)],
                ['label' => $mode === 'create' ? 'Novo' : 'Editar', 'href' => null],
            ],
            'quoteKind' => $kind,
            'mode' => $mode,
            'quote' => $quote,
            'items' => $lines,
            'clients' => $clients,
            'products' => $products,
            'services' => $services,
            'statuses' => self::STATUSES,
            'errors' => $errors,
            'old' => [
                'client_id' => (string) $request->input('client_id', ''),
                'status' => (string) $request->input('status', 'open'),
                'discount_total' => (string) $request->input('discount_total', '0'),
                'valid_until' => (string) $request->input('valid_until', ''),
                'issued_at' => (string) $request->input('issued_at', ''),
                'notes' => (string) $request->input('notes', ''),
            ],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseLines(Request $request, string $kind): array
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
            $qty = (float) str_replace(',', '.', (string) ($row['qty'] ?? '1'));
            $up = (float) str_replace(',', '.', (string) ($row['unit_price'] ?? '0'));
            $ld = (float) str_replace(',', '.', (string) ($row['line_discount'] ?? '0'));
            if ($qty <= 0) {
                continue;
            }
            if ($kind === 'product') {
                $pid = (int) ($row['product_id'] ?? 0);
                if ($pid < 1) {
                    continue;
                }
                $out[] = [
                    'product_id' => $pid,
                    'description' => isset($row['description']) ? trim((string) $row['description']) : null,
                    'qty' => $qty,
                    'unit_price' => $up,
                    'line_discount' => max(0, $ld),
                ];
            } else {
                $sid = (int) ($row['service_id'] ?? 0);
                if ($sid < 1) {
                    continue;
                }
                $out[] = [
                    'service_id' => $sid,
                    'description' => isset($row['description']) ? trim((string) $row['description']) : null,
                    'qty' => $qty,
                    'unit_price' => $up,
                    'line_discount' => max(0, $ld),
                ];
            }
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
