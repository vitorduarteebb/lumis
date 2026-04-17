<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\SupplierRepository;

final class SuppliersController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', '');
        $status = $status === '1' || $status === '0' ? (string) $status : null;
        $page = max(1, (int) $request->input('page', 1));

        $repo = new SupplierRepository();
        $result = $repo->paginate($cid, $q, $status, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('suppliers/index', [
            'title' => 'Fornecedores',
            'pageTitle' => 'Fornecedores',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Fornecedores', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'statusFilter' => $status,
            'basePath' => '/cadastros/fornecedores',
            'queryParams' => array_filter([
                'q' => $q !== '' ? $q : null,
                'status' => $status,
            ]),
        ]);
    }

    public function create(Request $request): string
    {
        $this->requireCompany();

        return $this->view('suppliers/form', [
            'title' => 'Novo fornecedor',
            'pageTitle' => 'Novo fornecedor',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Fornecedores', 'href' => '/cadastros/fornecedores'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'supplier' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/fornecedores/novo');
        }

        $data = $this->extractSupplierPayload($request);
        $errors = $this->validateSupplier($data, false);
        if ($errors !== []) {
            return $this->view('suppliers/form', [
                'title' => 'Novo fornecedor',
                'pageTitle' => 'Novo fornecedor',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Cadastros', 'href' => null],
                    ['label' => 'Fornecedores', 'href' => '/cadastros/fornecedores'],
                    ['label' => 'Novo', 'href' => null],
                ],
                'mode' => 'create',
                'supplier' => null,
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        $repo = new SupplierRepository();
        $uid = auth_id();
        $id = $repo->insert($cid, $this->normalizeForDb($data), $uid);
        Session::flash('success', 'Fornecedor cadastrado com sucesso.');
        redirect('/cadastros/fornecedores/' . $id);
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/fornecedores');
        }
        $repo = new SupplierRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Fornecedor não encontrado.');
            redirect('/cadastros/fornecedores');
        }

        return $this->view('suppliers/show', [
            'title' => (string) $row['name'],
            'pageTitle' => (string) $row['name'],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Fornecedores', 'href' => '/cadastros/fornecedores'],
                ['label' => 'Detalhes', 'href' => null],
            ],
            'supplier' => $row,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/fornecedores');
        }
        $repo = new SupplierRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Fornecedor não encontrado.');
            redirect('/cadastros/fornecedores');
        }

        return $this->view('suppliers/form', [
            'title' => 'Editar fornecedor',
            'pageTitle' => 'Editar fornecedor',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Fornecedores', 'href' => '/cadastros/fornecedores'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'supplier' => $row,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/fornecedores');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/fornecedores');
        }
        $repo = new SupplierRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Fornecedor não encontrado.');
            redirect('/cadastros/fornecedores');
        }

        $data = $this->extractSupplierPayload($request);
        $errors = $this->validateSupplier($data, true);
        if ($errors !== []) {
            return $this->view('suppliers/form', [
                'title' => 'Editar fornecedor',
                'pageTitle' => 'Editar fornecedor',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Cadastros', 'href' => null],
                    ['label' => 'Fornecedores', 'href' => '/cadastros/fornecedores'],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'supplier' => $row,
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        $repo->update($id, $cid, $this->normalizeForDb($data), auth_id());
        Session::flash('success', 'Fornecedor atualizado com sucesso.');
        redirect('/cadastros/fornecedores/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/fornecedores');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/fornecedores');
        }
        $repo = new SupplierRepository();
        if ($repo->findByIdForCompany($id, $cid) === null) {
            Session::flash('error', 'Fornecedor não encontrado.');
            redirect('/cadastros/fornecedores');
        }
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Fornecedor removido (exclusão lógica).');
        redirect('/cadastros/fornecedores');
    }

    /**
     * @return array<string, mixed>
     */
    private function extractSupplierPayload(Request $request): array
    {
        $pt = (string) $request->input('person_type', 'J');
        $pt = $pt === 'F' ? 'F' : 'J';

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
    private function validateSupplier(array $data, bool $isEdit): array
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
