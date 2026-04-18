<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ClientRepository;
use App\Repositories\ContractRentalRepository;
use App\Repositories\ContractServiceRepository;
use App\Repositories\ContractSubscriptionRepository;
use App\Repositories\LookupRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\StoreRepository;

final class ContratosController extends Controller
{
    private const PER_PAGE = 15;

    public function servicos(Request $request): string
    {
        return $this->serviceIndex($request);
    }

    public function servicosNovo(Request $request): string
    {
        return $this->serviceNovo($request);
    }

    public function servicosStore(Request $request): string
    {
        return $this->serviceStore($request);
    }

    public function servicosShow(Request $request): string
    {
        return $this->serviceShow($request);
    }

    public function servicosEditar(Request $request): string
    {
        return $this->serviceEditar($request);
    }

    public function servicosUpdate(Request $request): string
    {
        return $this->serviceUpdate($request);
    }

    public function servicosCancelar(Request $request): string
    {
        return $this->serviceCancelar($request);
    }

    public function servicosSuspender(Request $request): string
    {
        return $this->serviceSuspender($request);
    }

    public function locacoes(Request $request): string
    {
        return $this->rentalIndex($request);
    }

    public function locacoesNovo(Request $request): string
    {
        return $this->rentalNovo($request);
    }

    public function locacoesStore(Request $request): string
    {
        return $this->rentalStore($request);
    }

    public function locacoesShow(Request $request): string
    {
        return $this->rentalShow($request);
    }

    public function locacoesEditar(Request $request): string
    {
        return $this->rentalEditar($request);
    }

    public function locacoesUpdate(Request $request): string
    {
        return $this->rentalUpdate($request);
    }

    public function locacoesEncerrar(Request $request): string
    {
        return $this->rentalEncerrar($request);
    }

    public function locacoesCancelar(Request $request): string
    {
        return $this->rentalCancelar($request);
    }

    public function assinaturas(Request $request): string
    {
        return $this->subscriptionIndex($request);
    }

    public function assinaturasNovo(Request $request): string
    {
        return $this->subscriptionNovo($request);
    }

    public function assinaturasStore(Request $request): string
    {
        return $this->subscriptionStore($request);
    }

    public function assinaturasShow(Request $request): string
    {
        return $this->subscriptionShow($request);
    }

    public function assinaturasEditar(Request $request): string
    {
        return $this->subscriptionEditar($request);
    }

    public function assinaturasUpdate(Request $request): string
    {
        return $this->subscriptionUpdate($request);
    }

    public function assinaturasSuspender(Request $request): string
    {
        return $this->subscriptionSuspender($request);
    }

    public function assinaturasReativar(Request $request): string
    {
        return $this->subscriptionReativar($request);
    }

    public function assinaturasCancelar(Request $request): string
    {
        return $this->subscriptionCancelar($request);
    }

    public function servicosAnexo(Request $request): never
    {
        $this->streamContractAttachment($request, '/contratos/servicos', static function (int $id, int $cid) {
            return (new ContractServiceRepository())->findById($id, $cid);
        });
    }

    public function locacoesAnexo(Request $request): never
    {
        $this->streamContractAttachment($request, '/contratos/locacoes', static function (int $id, int $cid) {
            return (new ContractRentalRepository())->findById($id, $cid);
        });
    }

    public function assinaturasAnexo(Request $request): never
    {
        $this->streamContractAttachment($request, '/contratos/assinaturas', static function (int $id, int $cid) {
            return (new ContractSubscriptionRepository())->findById($id, $cid);
        });
    }

    /**
     * @param callable(int, int): (array<string, mixed>|null) $loadRow
     */
    private function streamContractAttachment(Request $request, string $listPath, callable $loadRow): never
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect($listPath);
        }
        $row = $loadRow($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect($listPath);
        }
        $rel = (string) ($row['attachment_path'] ?? '');
        if ($rel === '') {
            Session::flash('error', 'Sem anexo.');
            redirect($listPath . '/' . $id);
        }
        $abs = base_path($rel);
        if (!is_file($abs)) {
            Session::flash('error', 'Arquivo não encontrado.');
            redirect($listPath . '/' . $id);
        }
        $mime = 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            if ($fi !== false) {
                $m = finfo_file($fi, $abs);
                finfo_close($fi);
                if (is_string($m) && $m !== '') {
                    $mime = $m;
                }
            }
        }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($abs) . '"');
        readfile($abs);
        exit;
    }

    private function serviceIndex(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $repo = new ContractServiceRepository();
        $result = $repo->paginate(
            $cid,
            trim((string) $request->input('q', '')),
            (string) $request->input('status', 'all'),
            ((int) $request->input('client_id', 0)) > 0 ? (int) $request->input('client_id') : null,
            trim((string) $request->input('date_from', '')) ?: null,
            trim((string) $request->input('date_to', '')) ?: null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;
        $clients = (new ClientRepository())->allForCompany($cid);

        return $this->view('contratos/servicos/index', [
            'title' => 'Contratos — Serviços',
            'pageTitle' => 'Contratos de serviços',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Serviços', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => trim((string) $request->input('q', '')),
            'statusFilter' => (string) $request->input('status', 'all'),
            'filterClientId' => (int) $request->input('client_id', 0),
            'dateFrom' => trim((string) $request->input('date_from', '')),
            'dateTo' => trim((string) $request->input('date_to', '')),
            'clients' => $clients,
            'basePath' => '/contratos/servicos',
            'queryParams' => array_filter([
                'q' => trim((string) $request->input('q', '')) !== '' ? trim((string) $request->input('q', '')) : null,
                'status' => (string) $request->input('status', 'all') !== 'all' ? (string) $request->input('status', 'all') : null,
                'client_id' => ((int) $request->input('client_id', 0)) > 0 ? (int) $request->input('client_id') : null,
                'date_from' => trim((string) $request->input('date_from', '')) ?: null,
                'date_to' => trim((string) $request->input('date_to', '')) ?: null,
            ]),
        ]);
    }

    private function serviceNovo(Request $request): string
    {
        $cid = $this->requireCompany();

        return $this->view('contratos/servicos/form', [
            'title' => 'Novo contrato de serviço',
            'pageTitle' => 'Novo contrato',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Serviços', 'href' => '/contratos/servicos'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'row' => null,
            'clients' => (new ClientRepository())->allForCompany($cid),
            'services' => (new ServiceRepository())->paginate($cid, '', null, 1, 3000)['rows'],
            'stores' => (new StoreRepository())->byCompanyId($cid),
            'periodicities' => $this->periodicityRows($cid),
        ]);
    }

    private function serviceStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/servicos/novo');
        }
        $data = $this->parseServiceData($request);
        if (((int) $data['client_id']) < 1) {
            Session::flash('error', 'Selecione o cliente.');
            redirect('/contratos/servicos/novo');
        }
        $repo = new ContractServiceRepository();
        try {
            $id = $repo->insert($cid, $data, auth_id());
            $this->saveUploadedContractFile($cid, 'service', $id, $request, static fn (string $p) => $repo->setAttachmentPath($id, $cid, $p, auth_id()));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect('/contratos/servicos/novo');
        }
        Session::flash('success', 'Contrato registrado.');
        redirect('/contratos/servicos/' . $id);
    }

    private function serviceShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/servicos');
        }
        $row = (new ContractServiceRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/contratos/servicos');
        }

        return $this->view('contratos/servicos/show', [
            'title' => 'Contrato de serviço',
            'pageTitle' => (string) ($row['contract_number'] ?? 'Contrato'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Serviços', 'href' => '/contratos/servicos'],
                ['label' => (string) ($row['contract_number'] ?? '#' . $id), 'href' => null],
            ],
            'row' => $row,
        ]);
    }

    private function serviceEditar(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/servicos');
        }
        $row = (new ContractServiceRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/contratos/servicos');
        }

        return $this->view('contratos/servicos/form', [
            'title' => 'Editar contrato de serviço',
            'pageTitle' => 'Editar contrato',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Serviços', 'href' => '/contratos/servicos'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'row' => $row,
            'clients' => (new ClientRepository())->allForCompany($cid),
            'services' => (new ServiceRepository())->paginate($cid, '', null, 1, 3000)['rows'],
            'stores' => (new StoreRepository())->byCompanyId($cid),
            'periodicities' => $this->periodicityRows($cid),
        ]);
    }

    private function serviceUpdate(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/servicos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/servicos');
        }
        $data = $this->parseServiceData($request);
        if (((int) $data['client_id']) < 1) {
            Session::flash('error', 'Selecione o cliente.');
            redirect('/contratos/servicos/' . $id . '/editar');
        }
        $repo = new ContractServiceRepository();
        try {
            $repo->update($id, $cid, $data, auth_id());
            $this->saveUploadedContractFile($cid, 'service', $id, $request, static fn (string $p) => $repo->setAttachmentPath($id, $cid, $p, auth_id()));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect('/contratos/servicos/' . $id . '/editar');
        }
        Session::flash('success', 'Contrato atualizado.');
        redirect('/contratos/servicos/' . $id);
    }

    private function serviceCancelar(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/servicos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/servicos');
        }
        (new ContractServiceRepository())->setStatus($id, $cid, 'cancelled', auth_id());
        Session::flash('success', 'Contrato cancelado.');
        redirect('/contratos/servicos/' . $id);
    }

    private function serviceSuspender(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/servicos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/servicos');
        }
        (new ContractServiceRepository())->setStatus($id, $cid, 'suspended', auth_id());
        Session::flash('success', 'Contrato suspenso.');
        redirect('/contratos/servicos/' . $id);
    }

    /** @return array<string, mixed> */
    private function parseServiceData(Request $request): array
    {
        return [
            'store_id' => (int) $request->input('store_id', 0) > 0 ? (int) $request->input('store_id') : null,
            'contract_number' => trim((string) $request->input('contract_number', '')) ?: null,
            'client_id' => (int) $request->input('client_id', 0),
            'service_id' => (int) $request->input('service_id', 0) > 0 ? (int) $request->input('service_id') : null,
            'description' => trim((string) $request->input('description', '')) ?: null,
            'start_date' => $this->nullableDate((string) $request->input('start_date', '')),
            'end_date' => $this->nullableDate((string) $request->input('end_date', '')),
            'amount' => $this->parseMoney((string) $request->input('amount', '0')),
            'adjustment_note' => trim((string) $request->input('adjustment_note', '')) ?: null,
            'periodicity_entry_id' => (int) $request->input('periodicity_entry_id', 0) > 0 ? (int) $request->input('periodicity_entry_id') : null,
            'status' => (string) $request->input('status', 'active'),
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'attachment_path' => null,
        ];
    }

    private function rentalIndex(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $repo = new ContractRentalRepository();
        $result = $repo->paginate(
            $cid,
            trim((string) $request->input('q', '')),
            (string) $request->input('status', 'all'),
            ((int) $request->input('client_id', 0)) > 0 ? (int) $request->input('client_id') : null,
            trim((string) $request->input('date_from', '')) ?: null,
            trim((string) $request->input('date_to', '')) ?: null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;

        return $this->view('contratos/locacoes/index', [
            'title' => 'Contratos — Locações',
            'pageTitle' => 'Contratos de locação',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Locações', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => trim((string) $request->input('q', '')),
            'statusFilter' => (string) $request->input('status', 'all'),
            'filterClientId' => (int) $request->input('client_id', 0),
            'dateFrom' => trim((string) $request->input('date_from', '')),
            'dateTo' => trim((string) $request->input('date_to', '')),
            'clients' => (new ClientRepository())->allForCompany($cid),
            'basePath' => '/contratos/locacoes',
            'queryParams' => array_filter([
                'q' => trim((string) $request->input('q', '')) !== '' ? trim((string) $request->input('q', '')) : null,
                'status' => (string) $request->input('status', 'all') !== 'all' ? (string) $request->input('status', 'all') : null,
                'client_id' => ((int) $request->input('client_id', 0)) > 0 ? (int) $request->input('client_id') : null,
                'date_from' => trim((string) $request->input('date_from', '')) ?: null,
                'date_to' => trim((string) $request->input('date_to', '')) ?: null,
            ]),
        ]);
    }

    private function rentalNovo(Request $request): string
    {
        $cid = $this->requireCompany();

        return $this->view('contratos/locacoes/form', [
            'title' => 'Nova locação',
            'pageTitle' => 'Novo contrato de locação',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Locações', 'href' => '/contratos/locacoes'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'row' => null,
            'clients' => (new ClientRepository())->allForCompany($cid),
            'stores' => (new StoreRepository())->byCompanyId($cid),
            'periodicities' => $this->periodicityRows($cid),
        ]);
    }

    private function rentalStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/locacoes/novo');
        }
        $data = $this->parseRentalData($request);
        if (((int) $data['client_id']) < 1) {
            Session::flash('error', 'Selecione o cliente.');
            redirect('/contratos/locacoes/novo');
        }
        $repo = new ContractRentalRepository();
        try {
            $id = $repo->insert($cid, $data, auth_id());
            $this->saveUploadedContractFile($cid, 'rental', $id, $request, static fn (string $p) => $repo->setAttachmentPath($id, $cid, $p, auth_id()));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect('/contratos/locacoes/novo');
        }
        Session::flash('success', 'Contrato registrado.');
        redirect('/contratos/locacoes/' . $id);
    }

    private function rentalShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/locacoes');
        }
        $row = (new ContractRentalRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/contratos/locacoes');
        }

        return $this->view('contratos/locacoes/show', [
            'title' => 'Contrato de locação',
            'pageTitle' => (string) ($row['contract_number'] ?? 'Locação'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Locações', 'href' => '/contratos/locacoes'],
                ['label' => (string) ($row['contract_number'] ?? '#' . $id), 'href' => null],
            ],
            'row' => $row,
        ]);
    }

    private function rentalEditar(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/locacoes');
        }
        $row = (new ContractRentalRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/contratos/locacoes');
        }

        return $this->view('contratos/locacoes/form', [
            'title' => 'Editar locação',
            'pageTitle' => 'Editar contrato',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Locações', 'href' => '/contratos/locacoes'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'row' => $row,
            'clients' => (new ClientRepository())->allForCompany($cid),
            'stores' => (new StoreRepository())->byCompanyId($cid),
            'periodicities' => $this->periodicityRows($cid),
        ]);
    }

    private function rentalUpdate(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/locacoes');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/locacoes');
        }
        $data = $this->parseRentalData($request);
        if (((int) $data['client_id']) < 1) {
            Session::flash('error', 'Selecione o cliente.');
            redirect('/contratos/locacoes/' . $id . '/editar');
        }
        $repo = new ContractRentalRepository();
        try {
            $repo->update($id, $cid, $data, auth_id());
            $this->saveUploadedContractFile($cid, 'rental', $id, $request, static fn (string $p) => $repo->setAttachmentPath($id, $cid, $p, auth_id()));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect('/contratos/locacoes/' . $id . '/editar');
        }
        Session::flash('success', 'Contrato atualizado.');
        redirect('/contratos/locacoes/' . $id);
    }

    private function rentalEncerrar(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/locacoes');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/locacoes');
        }
        (new ContractRentalRepository())->setStatus($id, $cid, 'closed', auth_id());
        Session::flash('success', 'Contrato encerrado.');
        redirect('/contratos/locacoes/' . $id);
    }

    private function rentalCancelar(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/locacoes');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/locacoes');
        }
        (new ContractRentalRepository())->setStatus($id, $cid, 'cancelled', auth_id());
        Session::flash('success', 'Contrato cancelado.');
        redirect('/contratos/locacoes/' . $id);
    }

    /** @return array<string, mixed> */
    private function parseRentalData(Request $request): array
    {
        $dep = trim((string) $request->input('deposit_amount', ''));

        return [
            'store_id' => (int) $request->input('store_id', 0) > 0 ? (int) $request->input('store_id') : null,
            'contract_number' => trim((string) $request->input('contract_number', '')) ?: null,
            'client_id' => (int) $request->input('client_id', 0),
            'asset_description' => trim((string) $request->input('asset_description', '')) ?: null,
            'description' => trim((string) $request->input('description', '')) ?: null,
            'start_date' => $this->nullableDate((string) $request->input('start_date', '')),
            'end_date' => $this->nullableDate((string) $request->input('end_date', '')),
            'amount' => $this->parseMoney((string) $request->input('amount', '0')),
            'deposit_amount' => $dep !== '' ? $this->parseMoney($dep) : null,
            'periodicity_entry_id' => (int) $request->input('periodicity_entry_id', 0) > 0 ? (int) $request->input('periodicity_entry_id') : null,
            'status' => (string) $request->input('status', 'active'),
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'attachment_path' => null,
        ];
    }

    private function subscriptionIndex(Request $request): string
    {
        $cid = $this->requireCompany();
        $page = max(1, (int) $request->input('page', 1));
        $repo = new ContractSubscriptionRepository();
        $result = $repo->paginate(
            $cid,
            trim((string) $request->input('q', '')),
            (string) $request->input('status', 'all'),
            ((int) $request->input('client_id', 0)) > 0 ? (int) $request->input('client_id') : null,
            trim((string) $request->input('date_from', '')) ?: null,
            trim((string) $request->input('date_to', '')) ?: null,
            $page,
            self::PER_PAGE
        );
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / self::PER_PAGE) : 1;

        return $this->view('contratos/assinaturas/index', [
            'title' => 'Contratos — Assinaturas',
            'pageTitle' => 'Assinaturas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Assinaturas', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => trim((string) $request->input('q', '')),
            'statusFilter' => (string) $request->input('status', 'all'),
            'filterClientId' => (int) $request->input('client_id', 0),
            'dateFrom' => trim((string) $request->input('date_from', '')),
            'dateTo' => trim((string) $request->input('date_to', '')),
            'clients' => (new ClientRepository())->allForCompany($cid),
            'basePath' => '/contratos/assinaturas',
            'queryParams' => array_filter([
                'q' => trim((string) $request->input('q', '')) !== '' ? trim((string) $request->input('q', '')) : null,
                'status' => (string) $request->input('status', 'all') !== 'all' ? (string) $request->input('status', 'all') : null,
                'client_id' => ((int) $request->input('client_id', 0)) > 0 ? (int) $request->input('client_id') : null,
                'date_from' => trim((string) $request->input('date_from', '')) ?: null,
                'date_to' => trim((string) $request->input('date_to', '')) ?: null,
            ]),
        ]);
    }

    private function subscriptionNovo(Request $request): string
    {
        $cid = $this->requireCompany();

        return $this->view('contratos/assinaturas/form', [
            'title' => 'Nova assinatura',
            'pageTitle' => 'Nova assinatura',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Assinaturas', 'href' => '/contratos/assinaturas'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'row' => null,
            'clients' => (new ClientRepository())->allForCompany($cid),
            'stores' => (new StoreRepository())->byCompanyId($cid),
            'periodicities' => $this->periodicityRows($cid),
        ]);
    }

    private function subscriptionStore(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/assinaturas/novo');
        }
        $data = $this->parseSubscriptionData($request);
        if (((int) $data['client_id']) < 1) {
            Session::flash('error', 'Selecione o cliente.');
            redirect('/contratos/assinaturas/novo');
        }
        $repo = new ContractSubscriptionRepository();
        try {
            $id = $repo->insert($cid, $data, auth_id());
            $this->saveUploadedContractFile($cid, 'subscription', $id, $request, static fn (string $p) => $repo->setAttachmentPath($id, $cid, $p, auth_id()));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect('/contratos/assinaturas/novo');
        }
        Session::flash('success', 'Assinatura registrada.');
        redirect('/contratos/assinaturas/' . $id);
    }

    private function subscriptionShow(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/assinaturas');
        }
        $row = (new ContractSubscriptionRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/contratos/assinaturas');
        }

        return $this->view('contratos/assinaturas/show', [
            'title' => 'Assinatura',
            'pageTitle' => (string) ($row['subscription_number'] ?? 'Assinatura'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Assinaturas', 'href' => '/contratos/assinaturas'],
                ['label' => (string) ($row['subscription_number'] ?? '#' . $id), 'href' => null],
            ],
            'row' => $row,
        ]);
    }

    private function subscriptionEditar(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/assinaturas');
        }
        $row = (new ContractSubscriptionRepository())->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/contratos/assinaturas');
        }

        return $this->view('contratos/assinaturas/form', [
            'title' => 'Editar assinatura',
            'pageTitle' => 'Editar assinatura',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Assinaturas', 'href' => '/contratos/assinaturas'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'row' => $row,
            'clients' => (new ClientRepository())->allForCompany($cid),
            'stores' => (new StoreRepository())->byCompanyId($cid),
            'periodicities' => $this->periodicityRows($cid),
        ]);
    }

    private function subscriptionUpdate(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/assinaturas');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/assinaturas');
        }
        $data = $this->parseSubscriptionData($request);
        if (((int) $data['client_id']) < 1) {
            Session::flash('error', 'Selecione o cliente.');
            redirect('/contratos/assinaturas/' . $id . '/editar');
        }
        $repo = new ContractSubscriptionRepository();
        try {
            $repo->update($id, $cid, $data, auth_id());
            $this->saveUploadedContractFile($cid, 'subscription', $id, $request, static fn (string $p) => $repo->setAttachmentPath($id, $cid, $p, auth_id()));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            redirect('/contratos/assinaturas/' . $id . '/editar');
        }
        Session::flash('success', 'Assinatura atualizada.');
        redirect('/contratos/assinaturas/' . $id);
    }

    private function subscriptionSuspender(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/assinaturas');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/assinaturas');
        }
        (new ContractSubscriptionRepository())->setStatus($id, $cid, 'suspended', auth_id());
        Session::flash('success', 'Assinatura suspensa.');
        redirect('/contratos/assinaturas/' . $id);
    }

    private function subscriptionReativar(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/assinaturas');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/assinaturas');
        }
        (new ContractSubscriptionRepository())->setStatus($id, $cid, 'active', auth_id());
        Session::flash('success', 'Assinatura reativada.');
        redirect('/contratos/assinaturas/' . $id);
    }

    private function subscriptionCancelar(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/contratos/assinaturas');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/contratos/assinaturas');
        }
        (new ContractSubscriptionRepository())->setStatus($id, $cid, 'cancelled', auth_id());
        Session::flash('success', 'Assinatura cancelada.');
        redirect('/contratos/assinaturas/' . $id);
    }

    /** @return array<string, mixed> */
    private function parseSubscriptionData(Request $request): array
    {
        return [
            'store_id' => (int) $request->input('store_id', 0) > 0 ? (int) $request->input('store_id') : null,
            'subscription_number' => trim((string) $request->input('subscription_number', '')) ?: null,
            'client_id' => (int) $request->input('client_id', 0),
            'plan_description' => trim((string) $request->input('plan_description', '')) ?: null,
            'recurring_amount' => $this->parseMoney((string) $request->input('recurring_amount', '0')),
            'periodicity_entry_id' => (int) $request->input('periodicity_entry_id', 0) > 0 ? (int) $request->input('periodicity_entry_id') : null,
            'start_date' => $this->nullableDate((string) $request->input('start_date', '')),
            'next_billing_date' => $this->nullableDate((string) $request->input('next_billing_date', '')),
            'status' => (string) $request->input('status', 'active'),
            'notes' => trim((string) $request->input('notes', '')) ?: null,
            'attachment_path' => null,
        ];
    }

    /**
     * @param callable(string): void $onSaved
     */
    private function saveUploadedContractFile(int $companyId, string $kind, int $id, Request $request, callable $onSaved): void
    {
        if (!isset($_FILES['attachment']) || !is_array($_FILES['attachment'])) {
            return;
        }
        if ((int) ($_FILES['attachment']['error'] ?? 0) !== UPLOAD_ERR_OK) {
            return;
        }
        $tmp = (string) ($_FILES['attachment']['tmp_name'] ?? '');
        $name = (string) ($_FILES['attachment']['name'] ?? 'anexo');
        if ($tmp === '') {
            return;
        }
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $ext = $ext !== '' ? '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext) : '';
        $dir = base_path('storage/uploads/contracts/' . $companyId . '/' . $kind . '/' . $id);
        if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) {
            return;
        }
        $safe = 'anexo' . $ext;
        if ($safe === 'anexo') {
            $safe = 'anexo.bin';
        }
        if (!move_uploaded_file($tmp, $dir . '/' . $safe)) {
            return;
        }
        $rel = 'storage/uploads/contracts/' . $companyId . '/' . $kind . '/' . $id . '/' . $safe;
        $onSaved($rel);
    }

    /** @return list<array<string, mixed>> */
    private function periodicityRows(int $companyId): array
    {
        $r = (new LookupRepository())->paginateByType($companyId, 'contract_periodicity', '', 1, 500);

        return $r['rows'];
    }

    private function parseMoney(string $raw): float
    {
        $v = (float) str_replace(',', '.', preg_replace('/[^\d,.-]/', '', $raw));

        return max(0, $v);
    }

    private function nullableDate(string $d): ?string
    {
        $d = trim($d);

        return $d !== '' ? $d : null;
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
