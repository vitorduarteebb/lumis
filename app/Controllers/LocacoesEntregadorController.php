<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Response;
use App\Helpers\Session;
use App\Repositories\LookupRepository;
use App\Repositories\RentalOperationRepository;

final class LocacoesEntregadorController extends Controller
{
    private const PAINEL = '/locacoes/painel-entregador';

    public function index(Request $request): string
    {
        $cid = $this->requireCompany();
        $uid = auth_id();
        if ($uid === null) {
            redirect('/login');
        }
        $repo = new RentalOperationRepository();
        $status = (string) $request->input('status', 'all');
        $page = max(1, (int) $request->input('page', 1));
        $per = 20;
        $result = $repo->paginateForDeliveryUser($cid, $uid, $status, $page, $per);
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / $per)) : 1;
        $ts = $repo->maxUpdatedAtForDeliveryUser($cid, $uid);
        $presetNotes = $this->presetObservationLabels($cid);

        return $this->view('locacoes/entregador/index', [
            'title' => 'Minhas entregas',
            'pageTitle' => 'Painel do entregador',
            'rows' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'status' => $status,
            'statusLabels' => RentalOperationRepository::STATUS_LABELS,
            'typeLabels' => RentalOperationRepository::TYPE_LABELS,
            'pollTs' => $ts,
            'presetNotes' => $presetNotes,
        ], 'layouts/entregador');
    }

    public function show(Request $request): string
    {
        $cid = $this->requireCompany();
        $uid = auth_id();
        if ($uid === null) {
            redirect('/login');
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect(self::PAINEL);
        }
        $repo = new RentalOperationRepository();
        $row = $repo->findForDeliveryUser($id, $cid, $uid);
        if ($row === null) {
            Session::flash('error', 'Entrega não encontrada ou não atribuída a você.');
            redirect(self::PAINEL);
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
        $presetNotes = $this->presetObservationLabels($cid);

        return $this->view('locacoes/entregador/show', [
            'title' => (string) ($row['document_number'] ?? 'Entrega'),
            'pageTitle' => (string) ($row['document_number'] ?? 'Entrega'),
            'row' => $row,
            'items' => $items,
            'history' => $history,
            'mapsUrl' => $mapsUrl,
            'statusLabels' => RentalOperationRepository::STATUS_LABELS,
            'typeLabels' => RentalOperationRepository::TYPE_LABELS,
            'presetNotes' => $presetNotes,
        ], 'layouts/entregador');
    }

    public function atualizarStatus(Request $request): string
    {
        $cid = $this->requireCompany();
        $uid = auth_id();
        if ($uid === null) {
            redirect('/login');
        }
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect(self::PAINEL);
        }
        $id = $request->routeInt('id');
        if ($id === null || $id < 1) {
            redirect(self::PAINEL);
        }
        $action = (string) $request->input('action', '');
        $note = trim((string) $request->input('note', ''));
        $map = [
            'iniciar_rota' => 'in_route',
            'entregue' => 'delivered',
            'entregue_ressalvas' => 'delivered_issues',
            'coleta_pendente' => 'pickup_pending',
            'coletado' => 'collected',
        ];
        if (!isset($map[$action])) {
            Session::flash('error', 'Ação inválida.');
            redirect(self::PAINEL . '/' . $id);
        }
        $newStatus = $map[$action];
        $repo = new RentalOperationRepository();
        $ok = $repo->appendDriverNotesAndUpdateStatus(
            $id,
            $cid,
            $uid,
            $newStatus,
            $note !== '' ? $note : null,
            $note !== '' ? $note : null,
            $uid
        );
        if (!$ok) {
            Session::flash('error', 'Não foi possível atualizar.');
            redirect(self::PAINEL);
        }
        Session::flash('success', 'Status atualizado.');
        redirect(self::PAINEL . '/' . $id);
    }

    public function poll(Request $request): never
    {
        $cid = $this->requireCompany();
        $uid = auth_id();
        if ($uid === null) {
            Response::json(['ok' => false, 'error' => 'unauthorized'], 401);
        }
        $since = (string) $request->input('since', '');
        $repo = new RentalOperationRepository();
        $max = $repo->maxUpdatedAtForDeliveryUser($cid, $uid);
        $changed = $since === '' || ($max !== null && $max !== $since);

        Response::json([
            'ok' => true,
            'updated_at_max' => $max,
            'changed' => $changed,
        ]);
    }

    /**
     * @return list<string>
     */
    private function presetObservationLabels(int $companyId): array
    {
        $lookup = new LookupRepository();
        $rows = $lookup->paginateByType($companyId, 'locacoes_obs_default', '', 1, 200)['rows'] ?? [];
        $out = [];
        foreach ($rows as $r) {
            $n = trim((string) ($r['name'] ?? ''));
            if ($n !== '') {
                $out[] = $n;
            }
        }
        if ($out === []) {
            $out = [
                'Cliente não estava',
                'Entrega com ressalva',
                'Retirada agendada para outro dia',
                'Acesso difícil',
            ];
        }

        return $out;
    }

    private function requireCompany(): int
    {
        $cid = current_company_id();
        if ($cid === null) {
            Session::flash('error', 'Empresa não definida.');
            redirect('/login');
        }

        return $cid;
    }
}
