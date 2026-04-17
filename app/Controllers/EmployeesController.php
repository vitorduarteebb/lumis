<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\EmployeeRepository;

final class EmployeesController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', '');
        $status = $status === '1' || $status === '0' ? (string) $status : null;
        $page = max(1, (int) $request->input('page', 1));
        $repo = new EmployeeRepository();
        $result = $repo->paginate($cid, $q, $status, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('employees/index', [
            'title' => 'Funcionários',
            'pageTitle' => 'Funcionários',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Funcionários', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'statusFilter' => $status,
            'basePath' => '/cadastros/funcionarios',
            'queryParams' => array_filter(['q' => $q !== '' ? $q : null, 'status' => $status]),
        ]);
    }

    public function create(Request $request): string
    {
        $this->requireCompany();

        return $this->view('employees/form', [
            'title' => 'Novo funcionário',
            'pageTitle' => 'Novo funcionário',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Funcionários', 'href' => '/cadastros/funcionarios'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'employee' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/funcionarios/novo');
        }
        $data = $this->extractPayload($request);
        $errors = $this->validate($data);
        if ($errors !== []) {
            return $this->view('employees/form', [
                'title' => 'Novo funcionário',
                'pageTitle' => 'Novo funcionário',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Cadastros', 'href' => null],
                    ['label' => 'Funcionários', 'href' => '/cadastros/funcionarios'],
                    ['label' => 'Novo', 'href' => null],
                ],
                'mode' => 'create',
                'employee' => null,
                'errors' => $errors,
                'old' => $data,
            ]);
        }
        $repo = new EmployeeRepository();
        $id = $repo->insert($cid, $this->normalize($data), auth_id());
        Session::flash('success', 'Funcionário cadastrado.');
        redirect('/cadastros/funcionarios/' . $id);
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/funcionarios');
        }
        $repo = new EmployeeRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/cadastros/funcionarios');
        }

        return $this->view('employees/show', [
            'title' => (string) $row['name'],
            'pageTitle' => (string) $row['name'],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Funcionários', 'href' => '/cadastros/funcionarios'],
                ['label' => 'Detalhes', 'href' => null],
            ],
            'employee' => $row,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/funcionarios');
        }
        $repo = new EmployeeRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/cadastros/funcionarios');
        }

        return $this->view('employees/form', [
            'title' => 'Editar funcionário',
            'pageTitle' => 'Editar funcionário',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Funcionários', 'href' => '/cadastros/funcionarios'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'employee' => $row,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/funcionarios');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/funcionarios');
        }
        $repo = new EmployeeRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/cadastros/funcionarios');
        }
        $data = $this->extractPayload($request);
        $errors = $this->validate($data);
        if ($errors !== []) {
            return $this->view('employees/form', [
                'title' => 'Editar funcionário',
                'pageTitle' => 'Editar funcionário',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Cadastros', 'href' => null],
                    ['label' => 'Funcionários', 'href' => '/cadastros/funcionarios'],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'employee' => $row,
                'errors' => $errors,
                'old' => $data,
            ]);
        }
        $repo->update($id, $cid, $this->normalize($data), auth_id());
        Session::flash('success', 'Funcionário atualizado.');
        redirect('/cadastros/funcionarios/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/funcionarios');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/funcionarios');
        }
        $repo = new EmployeeRepository();
        if ($repo->findByIdForCompany($id, $cid) === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/cadastros/funcionarios');
        }
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Funcionário removido (exclusão lógica).');
        redirect('/cadastros/funcionarios');
    }

    /**
     * @return array<string, mixed>
     */
    private function extractPayload(Request $request): array
    {
        $hd = trim((string) $request->input('hire_date', ''));

        return [
            'name' => trim((string) $request->input('name', '')),
            'document' => trim((string) $request->input('document', '')),
            'job_title' => trim((string) $request->input('job_title', '')),
            'email' => trim((string) $request->input('email', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'hire_date' => $hd === '' ? null : $hd,
            'notes' => trim((string) $request->input('notes', '')),
            'status' => (int) $request->input('status', 1) === 0 ? 0 : 1,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Informe o nome.';
        }
        if ($data['email'] !== '' && !filter_var((string) $data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $n = static fn (?string $s): ?string => $s === '' || $s === null ? null : $s;

        return [
            'name' => $data['name'],
            'document' => $n($data['document'] ?? null),
            'job_title' => $n($data['job_title'] ?? null),
            'email' => $n($data['email'] ?? null),
            'phone' => $n($data['phone'] ?? null),
            'hire_date' => $data['hire_date'],
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
