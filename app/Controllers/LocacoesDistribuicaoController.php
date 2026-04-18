<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Csrf;
use App\Helpers\Session;
use App\Repositories\ClientRepository;
use App\Repositories\RentalOperationRepository;
use App\Repositories\UserRepository;

final class LocacoesDistribuicaoController extends Controller
{
    private const BASE = '/locacoes/distribuicao';

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
        $onlyUnassigned = (string) $request->input('only_unassigned', '1') !== '0';
        $page = max(1, (int) $request->input('page', 1));
        $per = lumis_list_per_page();
        $result = $repo->paginateForDistribution(
            $cid,
            $q,
            $status,
            $deliveryUserId,
            $clientId,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null,
            $district,
            $onlyUnassigned,
            $page,
            $per
        );
        $totalPages = $result['total'] > 0 ? max(1, (int) ceil($result['total'] / $per)) : 1;
        $drivers = (new UserRepository())->listDeliveryDriversForCompany($cid);
        if ($drivers === []) {
            $drivers = (new UserRepository())->listActiveForCompany($cid);
        }
        $counts = $repo->countsByDeliveryUser($cid);

        return $this->view('locacoes/distribuicao/index', [
            'title' => 'Distribuição de entregas',
            'pageTitle' => 'Distribuição de entregas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Locações', 'href' => null],
                ['label' => 'Distribuição', 'href' => null],
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
            'onlyUnassigned' => $onlyUnassigned,
            'statusLabels' => RentalOperationRepository::STATUS_LABELS,
            'typeLabels' => RentalOperationRepository::TYPE_LABELS,
            'drivers' => $drivers,
            'clients' => (new ClientRepository())->allForCompany($cid),
            'countsByDriver' => $counts,
            'basePath' => self::BASE,
        ]);
    }

    public function atribuir(Request $request): string
    {
        $cid = $this->requireCompany();
        if (!Csrf::validate($request->input('_csrf_token'))) {
            Session::flash('error', 'Sessão expirada.');
            redirect(self::BASE);
        }
        $idsRaw = $request->input('ids', []);
        if (!is_array($idsRaw)) {
            $idsRaw = [];
        }
        $ids = array_values(array_filter(array_map(static fn ($v) => (int) $v, $idsRaw), static fn ($v) => $v > 0));
        $duRaw = $request->input('assign_delivery_user_id', '');
        $deliveryUserId = $duRaw === '' || $duRaw === null ? null : (int) $duRaw;
        if ($deliveryUserId !== null && $deliveryUserId < 1) {
            $deliveryUserId = null;
        }
        if ($ids === []) {
            Session::flash('error', 'Selecione ao menos uma locação.');
            redirect(self::BASE);
        }
        $repo = new RentalOperationRepository();
        $n = $repo->assignBatch($cid, $ids, $deliveryUserId, auth_id());
        Session::flash('success', $n > 0 ? "Atribuição atualizada ({$n} registro(s))." : 'Nenhum registro alterado.');
        redirect(self::BASE);
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
