<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\LookupRepository;

/**
 * Opções auxiliares do financeiro (lookup_entries com tipos dedicados).
 */
final class FinanceiroLookupController extends Controller
{
    private const PER_PAGE = 20;

    /** @var array<string, string> */
    private const TYPES = [
        'finance_category' => 'Categorias financeiras',
        'cost_center' => 'Centros de custo',
        'payment_method' => 'Formas de pagamento',
    ];

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $type = (string) $request->input('type', 'finance_category');
        if (!isset(self::TYPES[$type])) {
            $type = 'finance_category';
        }
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $repo = new LookupRepository();
        $result = $repo->paginateByType($cid, $type, $q, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('financeiro/lookups/index', [
            'title' => 'Opções auxiliares — Financeiro',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'types' => self::TYPES,
            'currentType' => $type,
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'basePath' => '/financeiro/opcoes-auxiliares',
            'queryParams' => array_filter(['type' => $type, 'q' => $q !== '' ? $q : null]),
        ]);
    }

    public function create(Request $request): string
    {
        $this->requireCompany();
        $type = (string) $request->input('type', 'finance_category');
        if (!isset(self::TYPES[$type])) {
            $type = 'finance_category';
        }

        return $this->view('financeiro/lookups/form', [
            'title' => 'Novo item',
            'pageTitle' => 'Novo item — ' . self::TYPES[$type],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => '/financeiro/opcoes-auxiliares?type=' . rawurlencode($type)],
                ['label' => 'Novo', 'href' => null],
            ],
            'entryType' => $type,
            'typeLabel' => self::TYPES[$type],
            'mode' => 'create',
            'row' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/financeiro/opcoes-auxiliares');
        }
        $type = (string) $request->input('entry_type', '');
        if (!isset(self::TYPES[$type])) {
            $type = 'finance_category';
        }
        $name = trim((string) $request->input('name', ''));
        $slug = trim((string) $request->input('slug', ''));
        if ($slug === '') {
            $slug = lumis_slugify($name);
        }
        $sort = (int) $request->input('sort_order', 0);
        $status = (int) $request->input('status', 1) === 0 ? 0 : 1;
        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Informe o nome.';
        }
        $repo = new LookupRepository();
        if ($slug !== '' && $repo->slugExists($cid, $type, $slug, null)) {
            $errors['slug'] = 'Este identificador já existe para este tipo.';
        }
        if ($errors !== []) {
            return $this->view('financeiro/lookups/form', [
                'title' => 'Novo item',
                'pageTitle' => 'Novo item',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Financeiro', 'href' => null],
                    ['label' => 'Opções auxiliares', 'href' => '/financeiro/opcoes-auxiliares?type=' . rawurlencode($type)],
                    ['label' => 'Novo', 'href' => null],
                ],
                'entryType' => $type,
                'typeLabel' => self::TYPES[$type],
                'mode' => 'create',
                'row' => null,
                'errors' => $errors,
                'old' => ['name' => $name, 'slug' => $slug, 'sort_order' => $sort, 'status' => $status, 'entry_type' => $type],
            ]);
        }
        $repo->insert($cid, [
            'entry_type' => $type,
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sort,
            'status' => $status,
        ]);
        Session::flash('success', 'Item cadastrado.');
        redirect('/financeiro/opcoes-auxiliares?type=' . rawurlencode($type));
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/financeiro/opcoes-auxiliares');
        }
        $repo = new LookupRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Item não encontrado.');
            redirect('/financeiro/opcoes-auxiliares');
        }
        $type = (string) $row['entry_type'];

        return $this->view('financeiro/lookups/form', [
            'title' => 'Editar item',
            'pageTitle' => 'Editar — ' . (self::TYPES[$type] ?? $type),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => '/financeiro/opcoes-auxiliares?type=' . rawurlencode($type)],
                ['label' => 'Editar', 'href' => null],
            ],
            'entryType' => $type,
            'typeLabel' => self::TYPES[$type] ?? $type,
            'mode' => 'edit',
            'row' => $row,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/financeiro/opcoes-auxiliares');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/financeiro/opcoes-auxiliares');
        }
        $repo = new LookupRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Item não encontrado.');
            redirect('/financeiro/opcoes-auxiliares');
        }
        $type = (string) $row['entry_type'];
        $name = trim((string) $request->input('name', ''));
        $slug = trim((string) $request->input('slug', ''));
        if ($slug === '') {
            $slug = lumis_slugify($name);
        }
        $sort = (int) $request->input('sort_order', 0);
        $status = (int) $request->input('status', 1) === 0 ? 0 : 1;
        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Informe o nome.';
        }
        if ($slug !== '' && $repo->slugExists($cid, $type, $slug, $id)) {
            $errors['slug'] = 'Este identificador já existe para este tipo.';
        }
        if ($errors !== []) {
            return $this->view('financeiro/lookups/form', [
                'title' => 'Editar item',
                'pageTitle' => 'Editar item',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Financeiro', 'href' => null],
                    ['label' => 'Opções auxiliares', 'href' => '/financeiro/opcoes-auxiliares?type=' . rawurlencode($type)],
                    ['label' => 'Editar', 'href' => null],
                ],
                'entryType' => $type,
                'typeLabel' => self::TYPES[$type] ?? $type,
                'mode' => 'edit',
                'row' => $row,
                'errors' => $errors,
                'old' => ['name' => $name, 'slug' => $slug, 'sort_order' => $sort, 'status' => $status],
            ]);
        }
        $repo->update($id, $cid, [
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sort,
            'status' => $status,
        ]);
        Session::flash('success', 'Item atualizado.');
        redirect('/financeiro/opcoes-auxiliares?type=' . rawurlencode($type));
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/financeiro/opcoes-auxiliares');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/financeiro/opcoes-auxiliares');
        }
        $repo = new LookupRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Item não encontrado.');
            redirect('/financeiro/opcoes-auxiliares');
        }
        $type = (string) $row['entry_type'];
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Item removido.');
        redirect('/financeiro/opcoes-auxiliares?type=' . rawurlencode($type));
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
