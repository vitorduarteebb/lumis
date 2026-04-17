<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Helpers\Session;

final class DashboardController extends Controller
{
    public function index(Request $request): string
    {
        $name = (string) Session::get('user_name', 'Usuário');

        return $this->view('dashboard/index', [
            'title' => 'Painel',
            'pageTitle' => 'Painel',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Painel', 'href' => null],
            ],
            'userName' => $name,
            'kpis' => [
                ['label' => 'A receber hoje', 'value' => 'R$ 12.450,00', 'hint' => 'vs. ontem +4%', 'icon' => 'bi-arrow-down-circle'],
                ['label' => 'A pagar hoje', 'value' => 'R$ 3.210,00', 'hint' => '7 títulos', 'icon' => 'bi-arrow-up-circle'],
                ['label' => 'Vendas do mês', 'value' => 'R$ 184.920,00', 'hint' => 'Meta 92%', 'icon' => 'bi-graph-up'],
                ['label' => 'Recebimentos do mês', 'value' => 'R$ 142.300,00', 'hint' => 'Projetado +6%', 'icon' => 'bi-cash-coin'],
                ['label' => 'Pagamentos do mês', 'value' => 'R$ 98.120,00', 'hint' => '3 agendados', 'icon' => 'bi-wallet2'],
                ['label' => 'Estoque baixo', 'value' => '14 SKUs', 'hint' => 'abaixo do mínimo', 'icon' => 'bi-box-seam'],
                ['label' => 'O.S. em andamento', 'value' => '18', 'hint' => '5 atrasadas', 'icon' => 'bi-clipboard-check'],
                ['label' => 'Orçamentos pendentes', 'value' => '9', 'hint' => 'aguardando aprovação', 'icon' => 'bi-hourglass-split'],
            ],
            'chartSales' => [12, 19, 14, 22, 18, 26, 24, 30, 28, 34, 31, 38],
            'chartCashflow' => [8, 10, 7, 12, 9, 11, 13, 10, 14, 12, 15, 16],
            'activities' => [
                ['icon' => 'bi-receipt', 'title' => 'NF-e autorizada · Pedido #4821', 'meta' => 'Há 12 min · Fiscal'],
                ['icon' => 'bi-cart-check', 'title' => 'Venda finalizada · Balcão', 'meta' => 'Há 35 min · Vendas'],
                ['icon' => 'bi-cash', 'title' => 'Baixa em conta a receber', 'meta' => 'Há 1 h · Financeiro'],
                ['icon' => 'bi-box-arrow-in-down', 'title' => 'Entrada de estoque · NF 1122', 'meta' => 'Há 2 h · Estoque'],
            ],
            'alerts' => [
                ['tone' => 'warning', 'title' => 'Certificado A1 expira em 21 dias', 'text' => 'Renove para evitar bloqueio fiscal.'],
                ['tone' => 'danger', 'title' => '5 O.S. com SLA estourado', 'text' => 'Priorize atendimento ou reagende prazos.'],
                ['tone' => 'info', 'title' => 'Backup automático concluído', 'text' => 'Snapshot das 03:00 registrado com sucesso.'],
            ],
            'shortcuts' => [
                ['label' => 'Novo orçamento', 'icon' => 'bi-file-earmark-plus', 'href' => '#', 'disabled' => true],
                ['label' => 'Nova venda', 'icon' => 'bi-cart-plus', 'href' => '#', 'disabled' => true],
                ['label' => 'Abrir O.S.', 'icon' => 'bi-clipboard-plus', 'href' => '#', 'disabled' => true],
                ['label' => 'Contas a pagar', 'icon' => 'bi-credit-card', 'href' => '#', 'disabled' => true],
            ],
            'calendar' => [
                'month' => 'Abril',
                'year' => '2026',
                'leadingBlanks' => 2,
                'daysInMonth' => 30,
                'today' => 17,
            ],
        ]);
    }
}
