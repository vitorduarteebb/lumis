<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\CarrierRepository;

final class CarriersController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', '');
        $status = $status === '1' || $status === '0' ? (string) $status : null;
        $page = max(1, (int) $request->input('page', 1));
        $repo = new CarrierRepository();
        $result = $repo->paginate($cid, $q, $status, $page, self::PER_PAGE);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / self::PER_PAGE)) : 1;

        return $this->view('carriers/index', [
            'title' => 'Transportadoras',
            'pageTitle' => 'Transportadoras',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Transportadoras', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'statusFilter' => $status,
            'basePath' => '/cadastros/transportadoras',
            'queryParams' => array_filter(['q' => $q !== '' ? $q : null, 'status' => $status]),
        ]);
    }

    public function create(Request $request): string
    {
        $this->requireCompany();

        return $this->view('carriers/form', [
            'title' => 'Nova transportadora',
            'pageTitle' => 'Nova transportadora',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Transportadoras', 'href' => '/cadastros/transportadoras'],
                ['label' => 'Novo', 'href' => null],
            ],
            'mode' => 'create',
            'carrier' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/transportadoras/novo');
        }
        $data = $this->extractPayload($request);
        $errors = $this->validate($data);
        if ($errors !== []) {
            return $this->view('carriers/form', [
                'title' => 'Nova transportadora',
                'pageTitle' => 'Nova transportadora',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Cadastros', 'href' => null],
                    ['label' => 'Transportadoras', 'href' => '/cadastros/transportadoras'],
                    ['label' => 'Novo', 'href' => null],
                ],
                'mode' => 'create',
                'carrier' => null,
                'errors' => $errors,
                'old' => $data,
            ]);
        }
        $repo = new CarrierRepository();
        $id = $repo->insert($cid, $this->normalize($data), auth_id());
        Session::flash('success', 'Transportadora cadastrada.');
        redirect('/cadastros/transportadoras/' . $id);
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/transportadoras');
        }
        $repo = new CarrierRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/cadastros/transportadoras');
        }

        return $this->view('carriers/show', [
            'title' => (string) $row['legal_name'],
            'pageTitle' => (string) $row['legal_name'],
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Transportadoras', 'href' => '/cadastros/transportadoras'],
                ['label' => 'Detalhes', 'href' => null],
            ],
            'carrier' => $row,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/transportadoras');
        }
        $repo = new CarrierRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/cadastros/transportadoras');
        }

        return $this->view('carriers/form', [
            'title' => 'Editar transportadora',
            'pageTitle' => 'Editar transportadora',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Transportadoras', 'href' => '/cadastros/transportadoras'],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'carrier' => $row,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/transportadoras');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/transportadoras');
        }
        $repo = new CarrierRepository();
        $row = $repo->findByIdForCompany($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/cadastros/transportadoras');
        }
        $data = $this->extractPayload($request);
        $errors = $this->validate($data);
        if ($errors !== []) {
            return $this->view('carriers/form', [
                'title' => 'Editar transportadora',
                'pageTitle' => 'Editar transportadora',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Cadastros', 'href' => null],
                    ['label' => 'Transportadoras', 'href' => '/cadastros/transportadoras'],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'carrier' => $row,
                'errors' => $errors,
                'old' => $data,
            ]);
        }
        $repo->update($id, $cid, $this->normalize($data), auth_id());
        Session::flash('success', 'Transportadora atualizada.');
        redirect('/cadastros/transportadoras/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect('/cadastros/transportadoras');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect('/cadastros/transportadoras');
        }
        $repo = new CarrierRepository();
        if ($repo->findByIdForCompany($id, $cid) === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect('/cadastros/transportadoras');
        }
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Transportadora removida (exclusão lógica).');
        redirect('/cadastros/transportadoras');
    }

    /**
     * @return array<string, mixed>
     */
    private function extractPayload(Request $request): array
    {
        return [
            'legal_name' => trim((string) $request->input('legal_name', '')),
            'trade_name' => trim((string) $request->input('trade_name', '')),
            'document' => trim((string) $request->input('document', '')),
            'state_registration' => trim((string) $request->input('state_registration', '')),
            'email' => trim((string) $request->input('email', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'mobile' => trim((string) $request->input('mobile', '')),
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
    private function validate(array $data): array
    {
        $errors = [];
        if ($data['legal_name'] === '') {
            $errors['legal_name'] = 'Informe a razão social.';
        }
        if ($data['email'] !== '' && !filter_var((string) $data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }
        if ($data['state'] !== '' && strlen((string) $data['state']) !== 2) {
            $errors['state'] = 'UF com 2 letras.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $n = static fn (?string $s): ?string => $s === '' ? null : $s;

        return [
            'legal_name' => $data['legal_name'],
            'trade_name' => $n($data['trade_name'] ?? null),
            'document' => $n($data['document'] ?? null),
            'state_registration' => $n($data['state_registration'] ?? null),
            'email' => $n($data['email'] ?? null),
            'phone' => $n($data['phone'] ?? null),
            'mobile' => $n($data['mobile'] ?? null),
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
