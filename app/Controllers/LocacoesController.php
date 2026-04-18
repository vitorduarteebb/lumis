<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ClientRepository;
use App\Repositories\RentalOperationRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;

final class LocacoesController extends Controller
{
    private const BASE = '/locacoes/gerenciar';

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $repo = new RentalOperationRepository();
        $q = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', 'all');
        $deliveryUserId = (int) $request->input('delivery_user_id', 0);
        $clientId = (int) $request->input('client_id', 0);
        $dateFrom = (string) $request->input('date_from', '');
        $dateTo = (string) $request->input('date_to', '');
        $district = trim((string) $request->input('district', ''));
        $page = max(1, (int) $request->input('page', 1));
        $per = lumis_list_per_page();
        $result = $repo->paginate(
            $cid,
            $q,
            $status,
            $deliveryUserId,
            $clientId,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null,
            $district,
            $page,
            $per
        );
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / $per)) : 1;
        $drivers = (new UserRepository())->listDeliveryDriversForCompany($cid);
        if ($drivers === []) {
            $drivers = (new UserRepository())->listActiveForCompany($cid);
        }
        $clients = (new ClientRepository())->allForCompany($cid);

        return $this->view('locacoes/gerenciar/index', [
            'title' => 'Locações operacionais',
            'pageTitle' => 'Gerenciar locações',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Locações', 'href' => null],
                ['label' => 'Gerenciar', 'href' => null],
            ],
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $q,
            'status' => $status,
            'deliveryUserId' => $deliveryUserId,
            'clientId' => $clientId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'district' => $district,
            'statusLabels' => RentalOperationRepository::STATUS_LABELS,
            'typeLabels' => RentalOperationRepository::TYPE_LABELS,
            'drivers' => $drivers,
            'clients' => $clients,
            'basePath' => self::BASE,
        ]);
    }

    public function create(Request $request): string
    {
        $cid = $this->requireCompany();
        $drivers = (new UserRepository())->listDeliveryDriversForCompany($cid);
        if ($drivers === []) {
            $drivers = (new UserRepository())->listActiveForCompany($cid);
        }

        return $this->view('locacoes/gerenciar/form', [
            'title' => 'Nova locação',
            'pageTitle' => 'Nova locação',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Locações', 'href' => self::BASE],
                ['label' => 'Nova', 'href' => null],
            ],
            'mode' => 'create',
            'row' => null,
            'items' => [['product_name' => '', 'qty' => 1, 'notes' => '']],
            'clients' => (new ClientRepository())->allForCompany($cid),
            'stores' => (new StoreRepository())->byCompanyId($cid),
            'drivers' => $drivers,
            'statusLabels' => RentalOperationRepository::STATUS_LABELS,
            'typeLabels' => RentalOperationRepository::TYPE_LABELS,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect(self::BASE);
        }
        $repo = new RentalOperationRepository();
        $data = $this->collectPayload($request);
        $errors = $this->validatePayload($data);
        $items = $this->parseItems($request);
        if ($items === []) {
            $errors['items'] = 'Informe ao menos um item com descrição.';
        }
        if ($errors !== []) {
            return $this->view('locacoes/gerenciar/form', [
                'title' => 'Nova locação',
                'pageTitle' => 'Nova locação',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Locações', 'href' => self::BASE],
                    ['label' => 'Nova', 'href' => null],
                ],
                'mode' => 'create',
                'row' => null,
                'items' => $items === [] ? [['product_name' => '', 'qty' => 1, 'notes' => '']] : $items,
                'clients' => (new ClientRepository())->allForCompany($cid),
                'stores' => (new StoreRepository())->byCompanyId($cid),
                'drivers' => (new UserRepository())->listDeliveryDriversForCompany($cid) ?: (new UserRepository())->listActiveForCompany($cid),
                'statusLabels' => RentalOperationRepository::STATUS_LABELS,
                'typeLabels' => RentalOperationRepository::TYPE_LABELS,
                'errors' => $errors,
                'old' => $data,
            ]);
        }
        $data['document_number'] = $repo->nextDocumentNumber($cid);
        $data['created_by'] = auth_id();
        $id = $repo->insert($cid, $data);
        $repo->replaceItems($id, $items);
        Session::flash('success', 'Locação criada com sucesso.');
        redirect(self::BASE . '/' . $id);
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect(self::BASE);
        }
        $repo = new RentalOperationRepository();
        $row = $repo->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect(self::BASE);
        }
        $items = $repo->listItems($id);
        $history = $repo->listHistory($id);
        $mapsUrl = lumis_google_maps_url([
            'street' => $row['street'] ?? '',
            'address_number' => $row['address_number'] ?? '',
            'complement' => $row['complement'] ?? '',
            'district' => $row['district'] ?? '',
            'city' => $row['city'] ?? '',
            'state' => $row['state'] ?? '',
            'cep' => $row['cep'] ?? '',
        ],
            isset($row['latitude']) && $row['latitude'] !== null && $row['latitude'] !== ''
                ? (float) $row['latitude'] : null,
            isset($row['longitude']) && $row['longitude'] !== null && $row['longitude'] !== ''
                ? (float) $row['longitude'] : null
        );

        return $this->view('locacoes/gerenciar/show', [
            'title' => (string) ($row['document_number'] ?? 'Locação'),
            'pageTitle' => (string) ($row['document_number'] ?? 'Locação'),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Locações', 'href' => self::BASE],
                ['label' => (string) ($row['document_number'] ?? ''), 'href' => null],
            ],
            'row' => $row,
            'items' => $items,
            'history' => $history,
            'statusLabels' => RentalOperationRepository::STATUS_LABELS,
            'typeLabels' => RentalOperationRepository::TYPE_LABELS,
            'mapsUrl' => $mapsUrl,
        ]);
    }

    public function edit(Request $request): string
    {
        $cid = $this->requireCompany();
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect(self::BASE);
        }
        $repo = new RentalOperationRepository();
        $row = $repo->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect(self::BASE);
        }
        $items = $repo->listItems($id);
        if ($items === []) {
            $items = [['product_name' => '', 'qty' => 1, 'notes' => '', 'product_id' => null]];
        }

        return $this->view('locacoes/gerenciar/form', [
            'title' => 'Editar locação',
            'pageTitle' => 'Editar · ' . (string) ($row['document_number'] ?? ''),
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Locações', 'href' => self::BASE],
                ['label' => 'Editar', 'href' => null],
            ],
            'mode' => 'edit',
            'row' => $row,
            'items' => $items,
            'clients' => (new ClientRepository())->allForCompany($cid),
            'stores' => (new StoreRepository())->byCompanyId($cid),
            'drivers' => (new UserRepository())->listDeliveryDriversForCompany($cid) ?: (new UserRepository())->listActiveForCompany($cid),
            'statusLabels' => RentalOperationRepository::STATUS_LABELS,
            'typeLabels' => RentalOperationRepository::TYPE_LABELS,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function update(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect(self::BASE);
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect(self::BASE);
        }
        $repo = new RentalOperationRepository();
        $row = $repo->findById($id, $cid);
        if ($row === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect(self::BASE);
        }
        $data = $this->collectPayload($request);
        $errors = $this->validatePayload($data);
        $items = $this->parseItems($request);
        if ($items === []) {
            $errors['items'] = 'Informe ao menos um item com descrição.';
        }
        if ($errors !== []) {
            return $this->view('locacoes/gerenciar/form', [
                'title' => 'Editar locação',
                'pageTitle' => 'Editar locação',
                'breadcrumbs' => [
                    ['label' => 'Início', 'href' => '/dashboard'],
                    ['label' => 'Locações', 'href' => self::BASE],
                    ['label' => 'Editar', 'href' => null],
                ],
                'mode' => 'edit',
                'row' => array_merge($row, $data),
                'items' => $items,
                'clients' => (new ClientRepository())->allForCompany($cid),
                'stores' => (new StoreRepository())->byCompanyId($cid),
                'drivers' => (new UserRepository())->listDeliveryDriversForCompany($cid) ?: (new UserRepository())->listActiveForCompany($cid),
                'statusLabels' => RentalOperationRepository::STATUS_LABELS,
                'typeLabels' => RentalOperationRepository::TYPE_LABELS,
                'errors' => $errors,
                'old' => [],
            ]);
        }
        $data['updated_by'] = auth_id();
        $repo->update($id, $cid, $data);
        $repo->replaceItems($id, $items);
        Session::flash('success', 'Locação atualizada.');
        redirect(self::BASE . '/' . $id);
    }

    public function cancelar(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect(self::BASE);
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect(self::BASE);
        }
        $repo = new RentalOperationRepository();
        if ($repo->findById($id, $cid) === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect(self::BASE);
        }
        $repo->updateStatus($id, $cid, 'cancelled', 'Cancelado pelo usuário.', auth_id());
        Session::flash('success', 'Locação cancelada.');
        redirect(self::BASE . '/' . $id);
    }

    public function destroy(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect(self::BASE);
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect(self::BASE);
        }
        $repo = new RentalOperationRepository();
        if ($repo->findById($id, $cid) === null) {
            Session::flash('error', 'Registro não encontrado.');
            redirect(self::BASE);
        }
        $repo->softDelete($id, $cid);
        Session::flash('success', 'Locação removida (exclusão lógica).');
        redirect(self::BASE);
    }

    /**
     * @return array<string, mixed>
     */
    private function collectPayload(Request $request): array
    {
        $storeRaw = $request->input('store_id', '');
        $storeId = $storeRaw === '' || $storeRaw === null ? null : (int) $storeRaw;
        $duRaw = $request->input('delivery_user_id', '');
        $deliveryUserId = $duRaw === '' || $duRaw === null ? null : (int) $duRaw;
        $latRaw = trim((string) $request->input('latitude', ''));
        $lngRaw = trim((string) $request->input('longitude', ''));

        return [
            'store_id' => $storeId,
            'client_id' => (int) $request->input('client_id', 0),
            'rental_date' => (string) $request->input('rental_date', date('Y-m-d')),
            'expected_delivery_date' => ($v = (string) $request->input('expected_delivery_date', '')) !== '' ? $v : null,
            'expected_pickup_date' => ($v = (string) $request->input('expected_pickup_date', '')) !== '' ? $v : null,
            'cep' => trim((string) $request->input('cep', '')) ?: null,
            'street' => trim((string) $request->input('street', '')) ?: null,
            'address_number' => trim((string) $request->input('address_number', '')) ?: null,
            'complement' => trim((string) $request->input('complement', '')) ?: null,
            'district' => trim((string) $request->input('district', '')) ?: null,
            'city' => trim((string) $request->input('city', '')) ?: null,
            'state' => trim((string) $request->input('state', '')) ?: null,
            'reference' => trim((string) $request->input('reference', '')) ?: null,
            'latitude' => $latRaw !== '' && is_numeric($latRaw) ? (float) $latRaw : null,
            'longitude' => $lngRaw !== '' && is_numeric($lngRaw) ? (float) $lngRaw : null,
            'contact_name' => trim((string) $request->input('contact_name', '')) ?: null,
            'phone_primary' => trim((string) $request->input('phone_primary', '')) ?: null,
            'phone_secondary' => trim((string) $request->input('phone_secondary', '')) ?: null,
            'notes_internal' => trim((string) $request->input('notes_internal', '')) ?: null,
            'notes_driver' => trim((string) $request->input('notes_driver', '')) ?: null,
            'operation_type' => $this->normalizeType((string) $request->input('operation_type', 'both')),
            'status' => $this->normalizeStatus((string) $request->input('status', 'pending')),
            'delivery_user_id' => $deliveryUserId && $deliveryUserId > 0 ? $deliveryUserId : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validatePayload(array $data): array
    {
        $errors = [];
        if ($data['client_id'] < 1) {
            $errors['client_id'] = 'Selecione o cliente.';
        }
        if ($data['rental_date'] === '') {
            $errors['rental_date'] = 'Informe a data da locação.';
        }
        if (!in_array($data['operation_type'], ['delivery', 'pickup', 'both'], true)) {
            $errors['operation_type'] = 'Tipo inválido.';
        }
        if (!in_array($data['status'], RentalOperationRepository::STATUSES, true)) {
            $errors['status'] = 'Status inválido.';
        }

        return $errors;
    }

    private function normalizeType(string $t): string
    {
        return in_array($t, ['delivery', 'pickup', 'both'], true) ? $t : 'both';
    }

    private function normalizeStatus(string $s): string
    {
        return in_array($s, RentalOperationRepository::STATUSES, true) ? $s : 'pending';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseItems(Request $request): array
    {
        $body = $request->body();
        $raw = $body['items'] ?? null;
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $it) {
            if (!is_array($it)) {
                continue;
            }
            $out[] = [
                'product_id' => !empty($it['product_id']) ? (int) $it['product_id'] : null,
                'product_name' => trim((string) ($it['product_name'] ?? '')),
                'qty' => (float) ($it['qty'] ?? 1),
                'notes' => trim((string) ($it['notes'] ?? '')),
            ];
        }

        return $out;
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
