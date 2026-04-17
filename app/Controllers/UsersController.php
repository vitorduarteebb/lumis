<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\CompanyRepository;
use App\Repositories\RoleRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;

final class UsersController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): string
    {
        $cid = $this->requireCompanyOrRedirect();
        if ($cid === null) {
            return '';
        }

        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $users = new UserRepository();
        $result = $users->paginate($cid, $q, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0
            ? max(1, (int) ceil($result['total'] / self::PER_PAGE))
            : 1;

        return $this->view('users/index', [
            'title' => 'Usuários',
            'pageTitle' => 'Usuários',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Usuários', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'basePath' => '/configuracoes/usuarios',
            'queryParams' => array_filter(['q' => $q !== '' ? $q : null]),
        ]);
    }

    public function create(Request $request): string
    {
        $cid = $this->requireCompanyOrRedirect();
        if ($cid === null) {
            return '';
        }

        $roles = new RoleRepository()->allOrdered();
        $stores = (new StoreRepository())->byCompanyId($cid);
        $companies = (new CompanyRepository())->allActive();

        return $this->view('users/form', [
            'title' => 'Novo usuário',
            'pageTitle' => 'Novo usuário',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Usuários', 'href' => '/configuracoes/usuarios'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'user' => null,
            'roles' => $roles,
            'stores' => $stores,
            'companies' => $companies,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompanyOrRedirect();
        if ($cid === null) {
            return '';
        }
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada. Tente novamente.');
            redirect('/configuracoes/usuarios/novo');
        }

        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $status = (int) $request->input('status', 1) === 0 ? 0 : 1;
        $companyId = (int) $request->input('company_id', $cid);
        $storeIdRaw = $request->input('store_id', '');
        $storeId = $storeIdRaw === '' || $storeIdRaw === null ? null : (int) $storeIdRaw;
        $roleIds = self::roleIdsFromBody($request);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Informe o nome.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Senha com no mínimo 6 caracteres.';
        }

        $users = new UserRepository();
        if ($email !== '' && $users->emailExists($email)) {
            $errors['email'] = 'Este e-mail já está em uso.';
        }

        if ($errors !== []) {
            $roles = new RoleRepository()->allOrdered();
            $stores = (new StoreRepository())->byCompanyId($cid);
            $companies = (new CompanyRepository())->allActive();

            return $this->view('users/form', [
                'title' => 'Novo usuário',
                'pageTitle' => 'Novo usuário',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Configurações', 'href' => null],
                    ['label' => 'Usuários', 'href' => '/configuracoes/usuarios'],
                    ['label' => 'Novo', 'href' => null],
                ],
                'mode' => 'create',
                'user' => null,
                'roles' => $roles,
                'stores' => $stores,
                'companies' => $companies,
                'errors' => $errors,
                'old' => [
                    'name' => $name,
                    'email' => $email,
                    'status' => $status,
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'role_ids' => $roleIds,
                ],
            ]);
        }

        $uid = $users->insert([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'company_id' => $companyId,
            'store_id' => $storeId,
            'status' => $status,
        ]);
        $users->syncRoles($uid, $roleIds);

        Session::flash('success', 'Usuário criado com sucesso.');
        redirect('/configuracoes/usuarios');
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompanyOrRedirect();
        if ($cid === null) {
            return '';
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/configuracoes/usuarios');
        }

        $users = new UserRepository();
        $user = $users->findByIdForCompany($id, $cid);
        if ($user === null) {
            Session::flash('error', 'Usuário não encontrado.');
            redirect('/configuracoes/usuarios');
        }

        $labels = $users->roleLabelsForUser($id);

        return $this->view('users/show', [
            'title' => (string) $user['name'],
            'pageTitle' => (string) $user['name'],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Usuários', 'href' => '/configuracoes/usuarios'],
                ['label' => 'Detalhes', 'href' => null],
            ],
            'user' => $user,
            'roleLabels' => $labels,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompanyOrRedirect();
        if ($cid === null) {
            return '';
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/configuracoes/usuarios');
        }

        $users = new UserRepository();
        $user = $users->findByIdForCompany($id, $cid);
        if ($user === null) {
            Session::flash('error', 'Usuário não encontrado.');
            redirect('/configuracoes/usuarios');
        }

        $roles = new RoleRepository()->allOrdered();
        $stores = (new StoreRepository())->byCompanyId($cid);
        $companies = (new CompanyRepository())->allActive();
        $roleIds = $users->roleIdsForUser($id);

        return $this->view('users/form', [
            'title' => 'Editar usuário',
            'pageTitle' => 'Editar usuário',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Usuários', 'href' => '/configuracoes/usuarios'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'user' => $user,
            'roles' => $roles,
            'stores' => $stores,
            'companies' => $companies,
            'selectedRoleIds' => $roleIds,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompanyOrRedirect();
        if ($cid === null) {
            return '';
        }
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada. Tente novamente.');
            redirect('/configuracoes/usuarios');
        }

        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/configuracoes/usuarios');
        }

        $users = new UserRepository();
        $user = $users->findByIdForCompany($id, $cid);
        if ($user === null) {
            Session::flash('error', 'Usuário não encontrado.');
            redirect('/configuracoes/usuarios');
        }

        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $status = (int) $request->input('status', 1) === 0 ? 0 : 1;
        $companyId = (int) $request->input('company_id', $cid);
        $storeIdRaw = $request->input('store_id', '');
        $storeId = $storeIdRaw === '' || $storeIdRaw === null ? null : (int) $storeIdRaw;
        $roleIds = self::roleIdsFromBody($request);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Informe o nome.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }
        if ($password !== '' && strlen($password) < 6) {
            $errors['password'] = 'Senha com no mínimo 6 caracteres.';
        }
        if ($email !== '' && $users->emailExists($email, $id)) {
            $errors['email'] = 'Este e-mail já está em uso.';
        }

        if ($errors !== []) {
            $roles = new RoleRepository()->allOrdered();
            $stores = (new StoreRepository())->byCompanyId($cid);
            $companies = (new CompanyRepository())->allActive();

            return $this->view('users/form', [
                'title' => 'Editar usuário',
                'pageTitle' => 'Editar usuário',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Configurações', 'href' => null],
                    ['label' => 'Usuários', 'href' => '/configuracoes/usuarios'],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'user' => array_merge($user, [
                    'name' => $name,
                    'email' => $email,
                    'status' => $status,
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                ]),
                'roles' => $roles,
                'stores' => $stores,
                'companies' => $companies,
                'selectedRoleIds' => $roleIds,
                'errors' => $errors,
                'old' => [],
            ]);
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'status' => $status,
            'company_id' => $companyId,
            'store_id' => $storeId,
        ];
        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $users->updateUser($id, $data);
        $users->syncRoles($id, $roleIds);

        Session::flash('success', 'Usuário atualizado com sucesso.');
        redirect('/configuracoes/usuarios/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompanyOrRedirect();
        if ($cid === null) {
            return '';
        }
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/configuracoes/usuarios');
        }

        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/configuracoes/usuarios');
        }

        if ($id === auth_id()) {
            Session::flash('error', 'Você não pode excluir a própria conta.');
            redirect('/configuracoes/usuarios');
        }

        $users = new UserRepository();
        $user = $users->findByIdForCompany($id, $cid);
        if ($user === null) {
            Session::flash('error', 'Usuário não encontrado.');
            redirect('/configuracoes/usuarios');
        }

        $users->softDelete($id);
        Session::flash('success', 'Usuário inativado (exclusão lógica).');
        redirect('/configuracoes/usuarios');
    }

    /**
     * @return list<int>
     */
    private static function roleIdsFromBody(Request $request): array
    {
        $b = $request->body();
        if (!isset($b['role_ids']) || !is_array($b['role_ids'])) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($v) => (int) $v, $b['role_ids']), static fn ($v) => $v > 0));
    }

    private function requireCompanyOrRedirect(): ?int
    {
        $cid = current_company_id();
        if ($cid === null) {
            Session::flash('error', 'Empresa não definida na sessão.');
            redirect('/dashboard');
        }

        return $cid;
    }
}
