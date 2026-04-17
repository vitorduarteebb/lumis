<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ClientRepository;

final class ClientsController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', '');
        $status = $status === '1' || $status === '0' ? (string) $status : null;
        $page = max(1, (int) $request->input('page', 1));

        $repo = new ClientRepository();
        $result = $repo->paginate($cid, $q, $status, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('clients/index', [
            'title' => 'Clientes',
            'pageTitle' => 'Clientes',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Clientes', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'statusFilter' => $status,
            'basePath' => '/cadastros/clientes',
            'queryParams' => array_filter([
                'q' => $q !== '' ? $q : null,
                'status' => $status,
            ]),
        ]);
    }

    public function create(Request $request): string
    {
        $this->requireCompany();

        return $this->view('clients/form', [
            'title' => 'Novo cliente',
            'pageTitle' => 'Novo cliente',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Clientes', 'href' => '/cadastros/clientes'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'client' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/clientes/novo');
        }

        $data = $this->extractClientPayload($request);
        $errors = $this->validateClient($data, false);
        if ($errors !== []) {
            return $this->view('clients/form', [
                'title' => 'Novo cliente',
                'pageTitle' => 'Novo cliente',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Cadastros', 'href' => null],
                    ['label' => 'Clientes', 'href' => '/cadastros/clientes'],
                    ['label' => 'Novo', 'href' => null],
                ],
                'mode' => 'create',
                'client' => null,
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        $repo = new ClientRepository();
        $uid = auth_id();
        $id = $repo->insert($cid, $this->normalizeForDb($data), $uid);
        Session::flash('success', 'Cliente cadastrado com sucesso.');
        redirect('/cadastros/clientes/' . $id);
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/clientes');
        }
        $repo = new ClientRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Cliente não encontrado.');
            redirect('/cadastros/clientes');
        }

        return $this->view('clients/show', [
            'title' => (string) $row['name'],
            'pageTitle' => (string) $row['name'],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Clientes', 'href' => '/cadastros/clientes'],
                ['label' => 'Detalhes', 'href' => null],
            ],
            'client' => $row,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/clientes');
        }
        $repo = new ClientRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Cliente não encontrado.');
            redirect('/cadastros/clientes');
        }

        return $this->view('clients/form', [
            'title' => 'Editar cliente',
            'pageTitle' => 'Editar cliente',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Clientes', 'href' => '/cadastros/clientes'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'client' => $row,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/clientes');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/clientes');
        }
        $repo = new ClientRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Cliente não encontrado.');
            redirect('/cadastros/clientes');
        }

        $data = $this->extractClientPayload($request);
        $errors = $this->validateClient($data, true);
        if ($errors !== []) {
            return $this->view('clients/form', [
                'title' => 'Editar cliente',
                'pageTitle' => 'Editar cliente',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Cadastros', 'href' => null],
                    ['label' => 'Clientes', 'href' => '/cadastros/clientes'],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'client' => $row,
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        $repo->update($id, $cid, $this->normalizeForDb($data), auth_id());
        Session::flash('success', 'Cliente atualizado com sucesso.');
        redirect('/cadastros/clientes/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/clientes');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/clientes');
        }
        $repo = new ClientRepository();
        if ($repo->findByIdForCompany($id, $cid) === null) {
            Session::flash('error', 'Cliente não encontrado.');
            redirect('/cadastros/clientes');
        }
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Cliente removido (exclusão lógica).');
        redirect('/cadastros/clientes');
    }

    /**
     * @return array<string, mixed>
     */
    private function extractClientPayload(Request $request): array
    {
        $pt = (string) $request->input('person_type', 'F');
        $pt = $pt === 'J' ? 'J' : 'F';

        return [
            'person_type' => $pt,
            'name' => trim((string) $request->input('name', '')),
            'trade_name' => trim((string) $request->input('trade_name', '')),
            'document' => trim((string) $request->input('document', '')),
            'state_registration' => trim((string) $request->input('state_registration', '')),
            'email' => trim((string) $request->input('email', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'mobile' => trim((string) $request->input('mobile', '')),
            'contact_name' => trim((string) $request->input('contact_name', '')),
            'cep' => trim((string) $request->input('cep', '')),
            'street' => trim((string) $request->input('street', '')),
            'address_number' => trim((string) $request->input('address_number', '')),
            'complement' => trim((string) $request->input('complement', '')),
            'district' => trim((string) $request->input('district', '')),
            'city' => trim((string) $request->input('city', '')),
            'state' => strtoupper(substr(trim((string) $request->input('state', '')), 0, 2)),
            'notes' => trim((string) $request->input('notes', '')),
            'status' => (int) $request->input('status', 1) === 0 ? 0 : 1,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validateClient(array $data, bool $isEdit): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Informe o nome ou razão social.';
        }
        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }
        if ($data['state'] !== '' && strlen($data['state']) !== 2) {
            $errors['state'] = 'Use a UF com 2 letras.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeForDb(array $data): array
    {
        $n = static fn (?string $s): ?string => $s === '' ? null : $s;

        return [
            'person_type' => $data['person_type'],
            'name' => $data['name'],
            'trade_name' => $n($data['trade_name'] ?? null),
            'document' => $n($data['document'] ?? null),
            'state_registration' => $n($data['state_registration'] ?? null),
            'email' => $n($data['email'] ?? null),
            'phone' => $n($data['phone'] ?? null),
            'mobile' => $n($data['mobile'] ?? null),
            'contact_name' => $n($data['contact_name'] ?? null),
            'cep' => $n($data['cep'] ?? null),
            'street' => $n($data['street'] ?? null),
            'address_number' => $n($data['address_number'] ?? null),
            'complement' => $n($data['complement'] ?? null),
            'district' => $n($data['district'] ?? null),
            'city' => $n($data['city'] ?? null),
            'state' => $n($data['state'] ?? null),
            'notes' => $n($data['notes'] ?? null),
            'status' => $data['status'],
        ];
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
