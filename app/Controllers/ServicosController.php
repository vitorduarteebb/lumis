<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ServiceRepository;

final class ServicosController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', '');
        $status = $status === '1' || $status === '0' ? (string) $status : null;
        $page = max(1, (int) $request->input('page', 1));

        $repo = new ServiceRepository();
        $result = $repo->paginate($cid, $q, $status, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('servicos/index', [
            'title' => 'Serviços',
            'pageTitle' => 'Serviços',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Serviços', 'href' => null],
                ['label' => 'Gerenciar serviços', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'statusFilter' => $status,
            'basePath' => '/servicos',
            'queryParams' => array_filter([
                'q' => $q !== '' ? $q : null,
                'status' => $status,
            ]),
        ]);
    }

    public function create(Request $request): string
    {
        $this->requireCompany();

        return $this->view('servicos/form', [
            'title' => 'Novo serviço',
            'pageTitle' => 'Novo serviço',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Serviços', 'href' => null],
                ['label' => 'Gerenciar serviços', 'href' => '/servicos'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'service' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/servicos/novo');
        }

        $data = $this->extractServicePayload($request);
        $errors = $this->validateService($data);
        if ($errors !== []) {
            return $this->view('servicos/form', [
                'title' => 'Novo serviço',
                'pageTitle' => 'Novo serviço',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Serviços', 'href' => null],
                    ['label' => 'Gerenciar serviços', 'href' => '/servicos'],
                    ['label' => 'Novo', 'href' => null],
                ],
                'mode' => 'create',
                'service' => null,
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        $repo = new ServiceRepository();
        $id = $repo->insert($cid, $this->normalizeServiceForDb($data), auth_id());
        Session::flash('success', 'Serviço cadastrado com sucesso.');
        redirect('/servicos/' . $id);
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/servicos');
        }
        $repo = new ServiceRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Serviço não encontrado.');
            redirect('/servicos');
        }

        return $this->view('servicos/show', [
            'title' => (string) $row['name'],
            'pageTitle' => (string) $row['name'],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Serviços', 'href' => null],
                ['label' => 'Gerenciar serviços', 'href' => '/servicos'],
                ['label' => 'Detalhes', 'href' => null],
            ],
            'service' => $row,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/servicos');
        }
        $repo = new ServiceRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Serviço não encontrado.');
            redirect('/servicos');
        }

        return $this->view('servicos/form', [
            'title' => 'Editar serviço',
            'pageTitle' => 'Editar serviço',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Serviços', 'href' => null],
                ['label' => 'Gerenciar serviços', 'href' => '/servicos'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'service' => $row,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/servicos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/servicos');
        }
        $repo = new ServiceRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Serviço não encontrado.');
            redirect('/servicos');
        }

        $data = $this->extractServicePayload($request);
        $errors = $this->validateService($data);
        if ($errors !== []) {
            return $this->view('servicos/form', [
                'title' => 'Editar serviço',
                'pageTitle' => 'Editar serviço',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Serviços', 'href' => null],
                    ['label' => 'Gerenciar serviços', 'href' => '/servicos'],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'service' => $row,
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        $repo->update($id, $cid, $this->normalizeServiceForDb($data), auth_id());
        Session::flash('success', 'Serviço atualizado com sucesso.');
        redirect('/servicos/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/servicos');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/servicos');
        }
        $repo = new ServiceRepository();
        if ($repo->findByIdForCompany($id, $cid) === null) {
            Session::flash('error', 'Serviço não encontrado.');
            redirect('/servicos');
        }
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Serviço removido (exclusão lógica).');
        redirect('/servicos');
    }

    /**
     * @return array<string, mixed>
     */
    private function extractServicePayload(Request $request): array
    {
        $durRaw = trim((string) $request->input('duration_minutes', ''));

        return [
            'name' => trim((string) $request->input('name', '')),
            'category' => trim((string) $request->input('category', '')),
            'price' => trim((string) $request->input('price', '0')),
            'duration_minutes' => $durRaw,
            'description' => trim((string) $request->input('description', '')),
            'status' => (int) $request->input('status', 1) === 0 ? 0 : 1,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validateService(array $data): array
    {
        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Informe o nome do serviço.';
        }
        $price = str_replace(',', '.', (string) $data['price']);
        if ($price === '' || !is_numeric($price)) {
            $errors['price'] = 'Informe um preço válido.';
        }
        $dm = (string) $data['duration_minutes'];
        if ($dm !== '' && (!ctype_digit($dm) || (int) $dm < 0)) {
            $errors['duration_minutes'] = 'Duração deve ser um número inteiro de minutos.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeServiceForDb(array $data): array
    {
        $n = static fn (?string $s): ?string => $s === '' ? null : $s;
        $price = (string) ((float) str_replace(',', '.', (string) $data['price']));
        $dm = (string) $data['duration_minutes'];
        $duration = $dm === '' ? null : (int) $dm;

        return [
            'name' => $data['name'],
            'category' => $n($data['category'] ?? null),
            'price' => $price,
            'duration_minutes' => $duration,
            'description' => $n($data['description'] ?? null),
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
