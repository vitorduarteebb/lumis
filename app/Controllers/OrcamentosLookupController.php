<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\LookupRepository;

/**
 * Opções auxiliares de orçamentos (lookup_entries).
 */
final class OrcamentosLookupController extends Controller
{
    private const PER_PAGE = 20;

    /** @var array<string, string> */
    private const TYPES = [
        'quote_status' => 'Status de orçamento',
        'quote_payment_condition' => 'Condições de pagamento',
        'quote_note_template' => 'Modelos de observações',
    ];

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $type = (string) $request->input('type', 'quote_status');
        if (!isset(self::TYPES[$type])) {
            $type = 'quote_status';
        }
        $q = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $repo = new LookupRepository();
        $result = $repo->paginateByType($cid, $type, $q, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('orcamentos/lookups/index', [
            'title' => 'Opções auxiliares — Orçamentos',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'types' => self::TYPES,
            'currentType' => $type,
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'basePath' => '/orcamentos/opcoes-auxiliares',
            'queryParams' => array_filter(['type' => $type, 'q' => $q !== '' ? $q : null]),
        ]);
    }

    public function create(Request $request): string
    {
        $this->requireCompany();
        $type = (string) $request->input('type', 'quote_status');
        if (!isset(self::TYPES[$type])) {
            $type = 'quote_status';
        }

        return $this->view('orcamentos/lookups/form', [
            'title' => 'Novo item',
            'pageTitle' => 'Novo — ' . self::TYPES[$type],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => '/orcamentos/opcoes-auxiliares?type=' . rawurlencode($type)],
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
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $type = (string) $request->input('entry_type', '');
        if (!isset(self::TYPES[$type])) {
            $type = 'quote_status';
        }
        $name = trim((string) $request->input('name', ''));
        $slug = trim((string) $request->input('slug', ''));
        if ($slug === '') {
            $slug = lumis_slugify($name);
        }
        $sort = (int) $request->input('sort_order', 0);
        $status = (int) $request->input('status', 1) === 0 ? 0 : 1;
        $valueText = $type === 'quote_note_template' ? trim((string) $request->input('value_text', '')) : null;
        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Informe o nome.';
        }
        $repo = new LookupRepository();
        if ($slug !== '' && $repo->slugExists($cid, $type, $slug, null)) {
            $errors['slug'] = 'Este identificador já existe para este tipo.';
        }
        if ($errors !== []) {
            return $this->view('orcamentos/lookups/form', [
                'title' => 'Novo item',
                'pageTitle' => 'Novo item',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Orçamentos', 'href' => null],
                    ['label' => 'Opções auxiliares', 'href' => '/orcamentos/opcoes-auxiliares?type=' . rawurlencode($type)],
                    ['label' => 'Novo', 'href' => null],
                ],
                'entryType' => $type,
                'typeLabel' => self::TYPES[$type],
                'mode' => 'create',
                'row' => null,
                'errors' => $errors,
                'old' => [
                    'name' => $name,
                    'slug' => $slug,
                    'sort_order' => $sort,
                    'status' => $status,
                    'entry_type' => $type,
                    'value_text' => $valueText ?? '',
                ],
            ]);
        }
        $row = [
            'entry_type' => $type,
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sort,
            'status' => $status,
        ];
        if ($type === 'quote_note_template') {
            $row['value_text'] = $valueText;
        }
        $repo->insert($cid, $row);
        Session::flash('success', 'Item cadastrado.');
        redirect('/orcamentos/opcoes-auxiliares?type=' . rawurlencode($type));
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $repo = new LookupRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Item não encontrado.');
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $type = (string) $row['entry_type'];

        return $this->view('orcamentos/lookups/form', [
            'title' => 'Editar item',
            'pageTitle' => 'Editar — ' . (self::TYPES[$type] ?? $type),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => '/orcamentos/opcoes-auxiliares?type=' . rawurlencode($type)],
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
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $repo = new LookupRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Item não encontrado.');
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $type = (string) $row['entry_type'];
        $name = trim((string) $request->input('name', ''));
        $slug = trim((string) $request->input('slug', ''));
        if ($slug === '') {
            $slug = lumis_slugify($name);
        }
        $sort = (int) $request->input('sort_order', 0);
        $status = (int) $request->input('status', 1) === 0 ? 0 : 1;
        $valueText = $type === 'quote_note_template' ? trim((string) $request->input('value_text', '')) : null;
        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Informe o nome.';
        }
        if ($slug !== '' && $repo->slugExists($cid, $type, $slug, $id)) {
            $errors['slug'] = 'Este identificador já existe para este tipo.';
        }
        if ($errors !== []) {
            return $this->view('orcamentos/lookups/form', [
                'title' => 'Editar item',
                'pageTitle' => 'Editar item',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Orçamentos', 'href' => null],
                    ['label' => 'Opções auxiliares', 'href' => '/orcamentos/opcoes-auxiliares?type=' . rawurlencode($type)],
                    ['label' => 'Editar', 'href' => null],
                ],
                'entryType' => $type,
                'typeLabel' => self::TYPES[$type] ?? $type,
                'mode' => 'edit',
                'row' => $row,
                'errors' => $errors,
                'old' => ['name' => $name, 'slug' => $slug, 'sort_order' => $sort, 'status' => $status, 'value_text' => $valueText ?? ''],
            ]);
        }
        $data = [
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sort,
            'status' => $status,
        ];
        if ($type === 'quote_note_template') {
            $data['value_text'] = $valueText;
        }
        $repo->update($id, $cid, $data);
        Session::flash('success', 'Item atualizado.');
        redirect('/orcamentos/opcoes-auxiliares?type=' . rawurlencode($type));
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $repo = new LookupRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Item não encontrado.');
            redirect('/orcamentos/opcoes-auxiliares');
        }
        $type = (string) $row['entry_type'];
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Item removido.');
        redirect('/orcamentos/opcoes-auxiliares?type=' . rawurlencode($type));
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
