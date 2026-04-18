<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ClientRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuoteRepository;
use App\Repositories\ServiceOrderRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\UserRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

final class OrdensServicoController extends Controller
{
    private const PER_PAGE = 15;

    /** @var list<string> */
    private const STATUSES = ['open', 'in_analysis', 'in_progress', 'waiting_part', 'done', 'delivered', 'cancelled'];

    /** @var list<string> */
    private const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $df = trim((string) $request->input('from', ''));
        $dt = trim((string) $request->input('to', ''));
        $page = max(1, (int) $request->input('page', 1));
        $repo = new ServiceOrderRepository();
        $result = $repo->paginate($cid, $q, $status, $df !== '' ? $df : null, $dt !== '' ? $dt : null, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('ordens_servico/index', [
            'title' => 'Ordens de serviço',
            'pageTitle' => 'Gerenciar O.S.',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => null],
                ['label' => 'Gerenciar O.S.', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'statusFilter' => $status,
            'dateFrom' => $df,
            'dateTo' => $dt,
            'basePath' => '/ordens-servico',
            'statuses' => self::STATUSES,
            'queryParams' => array_filter([
                'q' => $q !== '' ? $q : null,
                'status' => $status !== '' ? $status : null,
                'from' => $df !== '' ? $df : null,
                'to' => $dt !== '' ? $dt : null,
            ]),
        ]);
    }

    public function novo(Request $request): string
    {
        $cid = $this->requireCompany();
        $clients = (new ClientRepository())->listForSelect($cid);
        $users = (new UserRepository())->listActiveForCompany($cid);
        $products = (new ProductRepository())->listForSelect($cid);
        $services = (new ServiceRepository())->listForSelect($cid);
        $quotes = $this->fetchOpenServiceQuotes($cid);

        return $this->view('ordens_servico/form', [
            'title' => 'Nova ordem de serviço',
            'pageTitle' => 'Nova O.S.',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => '/ordens-servico'],
                ['label' => 'Nova', 'href' => null],
            ],
            'mode' => 'create',
            'order' => null,
            'items' => [],
            'clients' => $clients,
            'users' => $users,
            'products' => $products,
            'services' => $services,
            'quotes' => $quotes,
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/ordens-servico/novo');
        }
        $lines = $this->parseMixedLines($request);
        $clientId = (int) $request->input('client_id', 0);
        $quoteIdRaw = $request->input('quote_id', '');
        $quoteId = $quoteIdRaw !== '' && $quoteIdRaw !== null ? (int) $quoteIdRaw : null;
        $status = trim((string) $request->input('status', 'open'));
        if (!in_array($status, self::STATUSES, true)) {
            $status = 'open';
        }
        $priority = trim((string) $request->input('priority', 'normal'));
        if (!in_array($priority, self::PRIORITIES, true)) {
            $priority = 'normal';
        }
        $assigned = (int) $request->input('assigned_user_id', 0);
        $assigned = $assigned > 0 ? $assigned : null;
        $osType = trim((string) $request->input('os_type', ''));
        $osType = $osType !== '' ? $osType : null;
        $description = trim((string) $request->input('description', ''));
        $internal = trim((string) $request->input('internal_notes', ''));
        $customer = trim((string) $request->input('customer_notes', ''));
        $opened = $this->normalizeLocalDateTime((string) $request->input('opened_at', ''));
        $expected = $this->normalizeLocalDateTime((string) $request->input('expected_at', ''));
        $errors = [];
        if ($clientId < 1) {
            $errors['client_id'] = 'Selecione o cliente.';
        }
        if ($errors !== []) {
            return $this->refillOsForm(null, $lines, $errors, $request);
        }
        if ($quoteId !== null && $quoteId > 0) {
            $qr = new QuoteRepository();
            $qrow = $qr->findByIdForCompany($quoteId, $cid);
            if ($qrow === null || (string) $qrow['quote_kind'] !== 'service') {
                $quoteId = null;
            }
        } else {
            $quoteId = null;
        }
        $soRepo = new ServiceOrderRepository();
        $id = $soRepo->insert($cid, [
            'client_id' => $clientId,
            'quote_id' => $quoteId,
            'status' => $status,
            'priority' => $priority,
            'description' => $description !== '' ? $description : null,
            'internal_notes' => $internal !== '' ? $internal : null,
            'customer_notes' => $customer !== '' ? $customer : null,
            'assigned_user_id' => $assigned,
            'os_type' => $osType,
            'opened_at' => $opened ?? date('Y-m-d H:i:s'),
            'expected_at' => $expected,
            'completed_at' => null,
        ]);
        $soRepo->replaceItems($id, $lines);
        Session::flash('success', 'O.S. criada.');
        redirect('/ordens-servico/' . $id);
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/ordens-servico');
        }
        $soRepo = new ServiceOrderRepository();
        $row = $soRepo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'O.S. não encontrada.');
            redirect('/ordens-servico');
        }
        $items = $soRepo->getItems($id);
        $tot = 0.0;
        foreach ($items as $it) {
            $tot += (float) ($it['line_total'] ?? 0);
        }

        return $this->view('ordens_servico/show', [
            'title' => 'O.S. ' . (string) ($row['code'] ?? ''),
            'pageTitle' => (string) ($row['code'] ?? 'Ordem de serviço'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => '/ordens-servico'],
                ['label' => (string) ($row['code'] ?? ''), 'href' => null],
            ],
            'order' => $row,
            'items' => $items,
            'totalItems' => $tot,
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/ordens-servico');
        }
        $soRepo = new ServiceOrderRepository();
        $row = $soRepo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'O.S. não encontrada.');
            redirect('/ordens-servico');
        }
        if (in_array((string) $row['status'], ['delivered', 'cancelled'], true)) {
            Session::flash('error', 'O.S. encerrada — edição bloqueada.');
            redirect('/ordens-servico/' . $id);
        }
        $items = $soRepo->getItems($id);
        $clients = (new ClientRepository())->listForSelect($cid);
        $users = (new UserRepository())->listActiveForCompany($cid);
        $products = (new ProductRepository())->listForSelect($cid);
        $services = (new ServiceRepository())->listForSelect($cid);
        $quotes = $this->fetchOpenServiceQuotes($cid);

        return $this->view('ordens_servico/form', [
            'title' => 'Editar O.S.',
            'pageTitle' => 'Editar',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => '/ordens-servico'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'order' => $row,
            'items' => $items,
            'clients' => $clients,
            'users' => $users,
            'products' => $products,
            'services' => $services,
            'quotes' => $quotes,
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/ordens-servico');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/ordens-servico');
        }
        $soRepo = new ServiceOrderRepository();
        $row = $soRepo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'O.S. não encontrada.');
            redirect('/ordens-servico');
        }
        if (in_array((string) $row['status'], ['delivered', 'cancelled'], true)) {
            Session::flash('error', 'Alteração não permitida.');
            redirect('/ordens-servico/' . $id);
        }
        $lines = $this->parseMixedLines($request);
        $clientId = (int) $request->input('client_id', 0);
        $status = trim((string) $request->input('status', 'open'));
        if (!in_array($status, self::STATUSES, true)) {
            $status = 'open';
        }
        $priority = trim((string) $request->input('priority', 'normal'));
        if (!in_array($priority, self::PRIORITIES, true)) {
            $priority = 'normal';
        }
        $assigned = (int) $request->input('assigned_user_id', 0);
        $assigned = $assigned > 0 ? $assigned : null;
        $osType = trim((string) $request->input('os_type', ''));
        $osType = $osType !== '' ? $osType : null;
        $description = trim((string) $request->input('description', ''));
        $internal = trim((string) $request->input('internal_notes', ''));
        $customer = trim((string) $request->input('customer_notes', ''));
        $expected = $this->normalizeLocalDateTime((string) $request->input('expected_at', ''));
        $completed = $this->normalizeLocalDateTime((string) $request->input('completed_at', ''));
        $errors = [];
        if ($clientId < 1) {
            $errors['client_id'] = 'Selecione o cliente.';
        }
        if ($errors !== []) {
            return $this->refillOsForm($row, $lines, $errors, $request);
        }
        $completedAt = $completed;
        if (($status === 'done' || $status === 'delivered') && $completedAt === null) {
            $completedAt = date('Y-m-d H:i:s');
        }
        $soRepo->update($id, $cid, [
            'client_id' => $clientId,
            'status' => $status,
            'priority' => $priority,
            'description' => $description !== '' ? $description : null,
            'internal_notes' => $internal !== '' ? $internal : null,
            'customer_notes' => $customer !== '' ? $customer : null,
            'assigned_user_id' => $assigned,
            'os_type' => $osType,
            'expected_at' => $expected,
            'completed_at' => $completedAt,
        ]);
        $soRepo->replaceItems($id, $lines);
        Session::flash('success', 'O.S. atualizada.');
        redirect('/ordens-servico/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/ordens-servico');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/ordens-servico');
        }
        $soRepo = new ServiceOrderRepository();
        $row = $soRepo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'O.S. não encontrada.');
            redirect('/ordens-servico');
        }
        $soRepo->softDelete($id, $cid);
        Session::flash('success', 'O.S. cancelada.');
        redirect('/ordens-servico');
    }

    public function pdf(Request $request): void
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/ordens-servico');
        }
        $soRepo = new ServiceOrderRepository();
        $row = $soRepo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'O.S. não encontrada.');
            redirect('/ordens-servico');
        }
        $items = $soRepo->getItems($id);
        $total = 0.0;
        foreach ($items as $it) {
            $total += (float) ($it['line_total'] ?? 0);
        }
        ob_start();
        include base_path('app/Views/ordens_servico/pdf.php');
        $html = (string) ob_get_clean();
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $code = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($row['code'] ?? (string) $id));
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="os-' . $code . '.pdf"');
        echo $dompdf->output();
        exit;
    }

    public function painel(Request $request): string
    {
        $cid = $this->requireCompany();
        $df = trim((string) $request->input('from', ''));
        $dt = trim((string) $request->input('to', ''));
        $tech = (int) $request->input('tech', 0);
        $client = (int) $request->input('client', 0);
        $repo = new ServiceOrderRepository();
        $counts = $repo->countByStatus($cid);
        $recent = $repo->recent($cid, 12);
        $overdue = $repo->overdue($cid, 15);
        $techF = $tech > 0 ? $tech : null;
        $clientF = $client > 0 ? $client : null;
        $openStatuses = ['open', 'in_analysis', 'in_progress', 'waiting_part'];
        $list = $repo->paginate(
            $cid,
            '',
            'all',
            $df !== '' ? $df : null,
            $dt !== '' ? $dt : null,
            1,
            30,
            $techF,
            $clientF
        );

        return $this->view('ordens_servico/painel', [
            'title' => 'Painel — Ordens de serviço',
            'pageTitle' => 'Painel',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => null],
                ['label' => 'Painel', 'href' => null],
            ],
            'counts' => $counts,
            'recent' => $recent,
            'overdue' => $overdue,
            'filteredRows' => $list['rows'],
            'dateFrom' => $df,
            'dateTo' => $dt,
            'techFilter' => $tech,
            'clientFilter' => $client,
            'technicians' => (new UserRepository())->listActiveForCompany($cid),
            'clients' => (new ClientRepository())->listForSelect($cid),
            'statusLabels' => self::statusLabels(),
            'openStatuses' => $openStatuses,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function statusLabels(): array
    {
        return [
            'open' => 'Aberta',
            'in_analysis' => 'Em análise',
            'in_progress' => 'Em andamento',
            'waiting_part' => 'Aguardando peça',
            'done' => 'Concluída',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelada',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchOpenServiceQuotes(int $companyId): array
    {
        $repo = new QuoteRepository();
        $r = $repo->paginate($companyId, 'service', '', 'all', null, null, 1, 100);
        $out = [];
        foreach ($r['rows'] as $row) {
            $st = (string) ($row['status'] ?? '');
            if (in_array($st, ['converted', 'cancelled'], true)) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $lines
     * @param array<string, string> $errors
     */
    private function refillOsForm(?array $order, array $lines, array $errors, Request $request): string
    {
        $cid = $this->requireCompany();
        $clients = (new ClientRepository())->listForSelect($cid);
        $users = (new UserRepository())->listActiveForCompany($cid);
        $products = (new ProductRepository())->listForSelect($cid);
        $services = (new ServiceRepository())->listForSelect($cid);
        $quotes = $this->fetchOpenServiceQuotes($cid);

        return $this->view('ordens_servico/form', [
            'title' => 'Corrigir O.S.',
            'pageTitle' => 'Ordem de serviço',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => '/ordens-servico'],
                ['label' => $order === null ? 'Nova' : 'Editar', 'href' => null],
            ],
            'mode' => $order === null ? 'create' : 'edit',
            'order' => $order,
            'items' => $lines,
            'clients' => $clients,
            'users' => $users,
            'products' => $products,
            'services' => $services,
            'quotes' => $quotes,
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'errors' => $errors,
            'old' => [
                'client_id' => (string) $request->input('client_id', ''),
                'quote_id' => (string) $request->input('quote_id', ''),
                'status' => (string) $request->input('status', 'open'),
                'priority' => (string) $request->input('priority', 'normal'),
                'assigned_user_id' => (string) $request->input('assigned_user_id', ''),
                'os_type' => (string) $request->input('os_type', ''),
                'description' => (string) $request->input('description', ''),
                'internal_notes' => (string) $request->input('internal_notes', ''),
                'customer_notes' => (string) $request->input('customer_notes', ''),
                'opened_at' => (string) $request->input('opened_at', ''),
                'expected_at' => (string) $request->input('expected_at', ''),
                'completed_at' => (string) $request->input('completed_at', ''),
            ],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseMixedLines(Request $request): array
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
            $lineType = (string) ($row['line_type'] ?? 'service');
            if ($qty <= 0) {
                continue;
            }
            if ($lineType === 'product') {
                $pid = (int) ($row['product_id'] ?? 0);
                if ($pid < 1) {
                    continue;
                }
                $out[] = [
                    'product_id' => $pid,
                    'service_id' => null,
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
                    'product_id' => null,
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

    private function normalizeLocalDateTime(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $s = str_replace('T', ' ', $raw);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $s) === 1) {
            $s .= ':00';
        }

        return $s;
    }
}
