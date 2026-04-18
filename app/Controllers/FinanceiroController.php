<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\AccountPayableRepository;
use App\Repositories\AccountReceivableRepository;
use App\Repositories\BankSlipRepository;
use App\Repositories\ClientRepository;
use App\Repositories\FinancialReportRepository;
use App\Repositories\SupplierRepository;

final class FinanceiroController extends Controller
{
    private const PER_PAGE = 15;

    public function contasPagar(Request $request): string
    {
        $cid = $this->requireCompany();
        $status = trim((string) $request->input('status', ''));
        $status = in_array($status, ['open', 'paid', 'cancelled'], true) ? $status : '';
        $page = max(1, (int) $request->input('page', 1));

        $repo = new AccountPayableRepository();
        $result = $repo->paginate($cid, $status, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;
        $suppliers = (new SupplierRepository())->listForSelect($cid);
        $editId = max(0, (int) $request->input('edit', 0));
        $editRow = $editId > 0 ? $repo->findById($editId, $cid) : null;

        return $this->view('financeiro/contas_pagar', [
            'title' => 'Contas a pagar',
            'pageTitle' => 'Contas a pagar',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Contas a pagar', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'statusFilter' => $status,
            'suppliers' => $suppliers,
            'editRow' => $editRow,
            'basePath' => '/financeiro/contas-pagar',
            'queryParams' => array_filter(['status' => $status !== '' ? $status : null]),
        ]);
    }

    public function contasPagarPost(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/financeiro/contas-pagar');
        }
        $action = (string) $request->input('_action', '');
        $repo = new AccountPayableRepository();

        if ($action === 'store') {
            $desc = trim((string) $request->input('description', ''));
            if ($desc === '') {
                Session::flash('error', 'Informe a descrição do título.');
                redirect('/financeiro/contas-pagar');
            }
            $repo->insert($cid, [
                'supplier_id' => $request->input('supplier_id'),
                'description' => $desc,
                'amount' => $this->parseDecimal((string) $request->input('amount', '0')),
                'due_date' => $this->parseDate((string) $request->input('due_date', '')),
                'status' => 'open',
            ]);
            Session::flash('success', 'Título registrado.');
            redirect('/financeiro/contas-pagar');
        }

        if ($action === 'update') {
            $id = (int) $request->input('id', 0);
            $row = $repo->findById($id, $cid);
            if ($row === null) {
                Session::flash('error', 'Registro não encontrado.');
                redirect('/financeiro/contas-pagar');
            }
            $desc = trim((string) $request->input('description', ''));
            if ($desc === '') {
                Session::flash('error', 'Informe a descrição do título.');
                redirect('/financeiro/contas-pagar?edit=' . $id);
            }
            $repo->update($id, $cid, [
                'supplier_id' => $request->input('supplier_id'),
                'description' => $desc,
                'amount' => $this->parseDecimal((string) $request->input('amount', '0')),
                'due_date' => $this->parseDate((string) $request->input('due_date', '')),
                'status' => (string) $request->input('status', 'open'),
            ]);
            Session::flash('success', 'Título atualizado.');
            redirect('/financeiro/contas-pagar');
        }

        if ($action === 'pay') {
            $id = (int) $request->input('id', 0);
            $amt = $this->parseDecimal((string) $request->input('payment_amount', '0'));
            if ($id < 1 || $amt <= 0) {
                Session::flash('error', 'Valor de pagamento inválido.');
                redirect('/financeiro/contas-pagar');
            }
            $repo->addPayment($id, $cid, (string) $amt);
            Session::flash('success', 'Pagamento registrado.');
            redirect('/financeiro/contas-pagar');
        }

        if ($action === 'delete') {
            $id = (int) $request->input('id', 0);
            if ($id > 0) {
                $repo->softDelete($id, $cid);
                Session::flash('success', 'Título removido.');
            }
            redirect('/financeiro/contas-pagar');
        }

        redirect('/financeiro/contas-pagar');
    }

    public function contasReceber(Request $request): string
    {
        $cid = $this->requireCompany();
        $status = trim((string) $request->input('status', ''));
        $status = in_array($status, ['open', 'paid', 'cancelled'], true) ? $status : '';
        $page = max(1, (int) $request->input('page', 1));

        $repo = new AccountReceivableRepository();
        $result = $repo->paginate($cid, $status, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;
        $clients = (new ClientRepository())->listForSelect($cid);
        $editId = max(0, (int) $request->input('edit', 0));
        $editRow = $editId > 0 ? $repo->findById($editId, $cid) : null;

        return $this->view('financeiro/contas_receber', [
            'title' => 'Contas a receber',
            'pageTitle' => 'Contas a receber',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Contas a receber', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'statusFilter' => $status,
            'clients' => $clients,
            'editRow' => $editRow,
            'basePath' => '/financeiro/contas-receber',
            'queryParams' => array_filter(['status' => $status !== '' ? $status : null]),
        ]);
    }

    public function contasReceberPost(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/financeiro/contas-receber');
        }
        $action = (string) $request->input('_action', '');
        $repo = new AccountReceivableRepository();

        if ($action === 'store') {
            $desc = trim((string) $request->input('description', ''));
            if ($desc === '') {
                Session::flash('error', 'Informe a descrição do título.');
                redirect('/financeiro/contas-receber');
            }
            $repo->insert($cid, [
                'client_id' => $request->input('client_id'),
                'description' => $desc,
                'amount' => $this->parseDecimal((string) $request->input('amount', '0')),
                'due_date' => $this->parseDate((string) $request->input('due_date', '')),
                'status' => 'open',
            ]);
            Session::flash('success', 'Título registrado.');
            redirect('/financeiro/contas-receber');
        }

        if ($action === 'update') {
            $id = (int) $request->input('id', 0);
            $row = $repo->findById($id, $cid);
            if ($row === null) {
                Session::flash('error', 'Registro não encontrado.');
                redirect('/financeiro/contas-receber');
            }
            $desc = trim((string) $request->input('description', ''));
            if ($desc === '') {
                Session::flash('error', 'Informe a descrição do título.');
                redirect('/financeiro/contas-receber?edit=' . $id);
            }
            $repo->update($id, $cid, [
                'client_id' => $request->input('client_id'),
                'description' => $desc,
                'amount' => $this->parseDecimal((string) $request->input('amount', '0')),
                'due_date' => $this->parseDate((string) $request->input('due_date', '')),
                'status' => (string) $request->input('status', 'open'),
            ]);
            Session::flash('success', 'Título atualizado.');
            redirect('/financeiro/contas-receber');
        }

        if ($action === 'receive') {
            $id = (int) $request->input('id', 0);
            $amt = $this->parseDecimal((string) $request->input('receipt_amount', '0'));
            if ($id < 1 || $amt <= 0) {
                Session::flash('error', 'Valor de recebimento inválido.');
                redirect('/financeiro/contas-receber');
            }
            $repo->addReceipt($id, $cid, (string) $amt);
            Session::flash('success', 'Recebimento registrado.');
            redirect('/financeiro/contas-receber');
        }

        if ($action === 'delete') {
            $id = (int) $request->input('id', 0);
            if ($id > 0) {
                $repo->softDelete($id, $cid);
                Session::flash('success', 'Título removido.');
            }
            redirect('/financeiro/contas-receber');
        }

        redirect('/financeiro/contas-receber');
    }

    public function dreGerencial(Request $request): string
    {
        $cid = $this->requireCompany();
        $month = trim((string) $request->input('mes', ''));
        if ($month === '' || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = (new \DateTimeImmutable('first day of this month'))->format('Y-m');
        }
        $start = $month . '-01';
        $end = (new \DateTimeImmutable($start))->format('Y-m-t');

        $repo = new FinancialReportRepository();
        $dre = $repo->dreSummary($cid, $start, $end);

        return $this->view('financeiro/dre', [
            'title' => 'DRE gerencial',
            'pageTitle' => 'DRE gerencial',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'DRE gerencial', 'href' => null],
            ],
            'mes' => $month,
            'periodoInicio' => $start,
            'periodoFim' => $end,
            'dre' => $dre,
        ]);
    }

    public function fluxoCaixa(Request $request): string
    {
        $cid = $this->requireCompany();
        $months = max(3, min(24, (int) $request->input('meses', 12)));
        $repo = new FinancialReportRepository();
        $series = $repo->cashFlowMonthly($cid, $months);

        return $this->view('financeiro/fluxo_caixa', [
            'title' => 'Fluxo de caixa',
            'pageTitle' => 'Fluxo de caixa',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Fluxo de caixa', 'href' => null],
            ],
            'meses' => $months,
            'series' => $series,
        ]);
    }

    public function boletosBancarios(Request $request): string
    {
        $cid = $this->requireCompany();
        $rows = (new BankSlipRepository())->listByCompany($cid);

        return $this->view('financeiro/boletos', [
            'title' => 'Boletos bancários',
            'pageTitle' => 'Boletos bancários',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Boletos bancários', 'href' => null],
            ],
            'rows' => $rows,
        ]);
    }

    public function boletosPost(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/financeiro/boletos-bancarios');
        }
        $action = (string) $request->input('_action', '');
        $repo = new BankSlipRepository();

        if ($action === 'store') {
            $repo->insert($cid, [
                'payer_name' => trim((string) $request->input('payer_name', '')),
                'amount' => $this->parseDecimal((string) $request->input('amount', '0')),
                'due_date' => $this->parseDate((string) $request->input('due_date', '')),
                'our_number' => trim((string) $request->input('our_number', '')),
                'notes' => trim((string) $request->input('notes', '')),
                'status' => 'pending',
            ]);
            Session::flash('success', 'Boleto registrado.');
            redirect('/financeiro/boletos-bancarios');
        }

        if ($action === 'status') {
            $id = (int) $request->input('id', 0);
            $st = (string) $request->input('status', 'pending');
            if ($id > 0 && in_array($st, ['pending', 'paid', 'cancelled'], true)) {
                $repo->updateStatus($id, $cid, $st);
                Session::flash('success', 'Status atualizado.');
            }
            redirect('/financeiro/boletos-bancarios');
        }

        redirect('/financeiro/boletos-bancarios');
    }

    private function parseDecimal(string $raw): float
    {
        $raw = trim($raw);
        $raw = preg_replace('/[^\d,.-]/', '', $raw) ?? '';
        $raw = str_replace('.', '', $raw);
        $raw = str_replace(',', '.', $raw);

        return (float) $raw;
    }

    private function parseDate(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return (new \DateTimeImmutable('today'))->format('Y-m-d');
        }
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $raw);

        return $dt !== false ? $dt->format('Y-m-d') : (new \DateTimeImmutable('today'))->format('Y-m-d');
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
