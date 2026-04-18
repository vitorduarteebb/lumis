<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Helpers\Session;
use App\Repositories\DashboardStatsRepository;

final class DashboardController extends Controller
{
    public function index(Request $request): string
    {
        if (function_exists('lumis_is_delivery_only_session') && lumis_is_delivery_only_session()) {
            redirect('/locacoes/painel-entregador');
        }

        $name = (string) Session::get('user_name', 'Usuário');
        $cid = current_company_id();
        if ($cid === null) {
            Session::flash('error', 'Empresa não definida na sessão.');
            redirect('/login');
        }

        $stats = new DashboardStatsRepository();
        $agg = $stats->aggregateForCompany($cid);
        $chartSales = $stats->salesLast12Months($cid);
        $chartCashflow = $stats->cashflowProxyLast12Months($cid);
        $chartLabels = $this->last12MonthLabels();

        $kpis = [
            [
                'label' => 'Contas a receber em aberto',
                'value' => lumis_money_br((float) $agg['ar_open']),
                'hint' => 'Soma de títulos em aberto',
                'icon' => 'bi-arrow-down-circle',
            ],
            [
                'label' => 'Contas a pagar em aberto',
                'value' => lumis_money_br((float) $agg['ap_open']),
                'hint' => 'Soma de títulos em aberto',
                'icon' => 'bi-arrow-up-circle',
            ],
            [
                'label' => 'Vendas no mês',
                'value' => lumis_money_br((float) $agg['sales_month']),
                'hint' => 'Documentos de venda (mês atual)',
                'icon' => 'bi-graph-up',
            ],
            [
                'label' => 'Clientes ativos',
                'value' => (string) (int) $agg['clients'],
                'hint' => 'Cadastros de clientes',
                'icon' => 'bi-people',
            ],
            [
                'label' => 'Produtos cadastrados',
                'value' => (string) (int) $agg['products'],
                'hint' => 'SKUs ativos no catálogo',
                'icon' => 'bi-box-seam',
            ],
            [
                'label' => 'Serviços cadastrados',
                'value' => (string) (int) $agg['services'],
                'hint' => 'Itens de serviço',
                'icon' => 'bi-wrench-adjustable',
            ],
            [
                'label' => 'Estoque abaixo do mínimo',
                'value' => (string) (int) $agg['low_stock'],
                'hint' => 'Produtos com estoque < mínimo',
                'icon' => 'bi-exclamation-triangle',
            ],
            [
                'label' => 'O.S. em aberto',
                'value' => (string) (int) $agg['os_open'],
                'hint' => 'Abertas ou em andamento',
                'icon' => 'bi-clipboard-check',
            ],
        ];

        $logs = $stats->recentAuditLogs(12);
        $activities = [];
        foreach ($logs as $log) {
            $activities[] = [
                'icon' => 'bi-activity',
                'title' => (string) ($log['description'] ?? $log['action'] ?? 'Evento'),
                'meta' => date('d/m/Y H:i', strtotime((string) $log['created_at'])) . ' · ' . ($log['module'] ?? ''),
            ];
        }

        $alerts = [];
        if ((int) $agg['low_stock'] > 0) {
            $alerts[] = [
                'tone' => 'warning',
                'title' => (int) $agg['low_stock'] . ' produto(s) com estoque abaixo do mínimo',
                'text' => 'Revise o catálogo em Produtos → Gerenciar produtos.',
            ];
        }
        if ($alerts === []) {
            $alerts[] = [
                'tone' => 'info',
                'title' => 'Nenhum alerta crítico',
                'text' => 'Indicadores dentro do esperado para o período.',
            ];
        }

        $shortcuts = [
            ['label' => 'Novo cliente', 'icon' => 'bi-person-plus', 'href' => '/cadastros/clientes/novo', 'disabled' => false],
            ['label' => 'Novo produto', 'icon' => 'bi-box-seam', 'href' => '/produtos/novo', 'disabled' => false],
            ['label' => 'Novo serviço', 'icon' => 'bi-wrench', 'href' => '/servicos/novo', 'disabled' => false],
            ['label' => 'Usuários', 'icon' => 'bi-person-gear', 'href' => '/configuracoes/usuarios', 'disabled' => false],
        ];

        $tickets = $stats->recentSupportTickets($cid, 5);
        $now = new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo'));
        $firstOfMonth = $now->modify('first day of this month');
        $leadingBlanks = max(0, (int) $firstOfMonth->format('N') - 1);
        $cal = [
            'month' => $this->monthNamePt((int) $now->format('n')),
            'year' => $now->format('Y'),
            'leadingBlanks' => $leadingBlanks,
            'daysInMonth' => (int) $now->format('t'),
            'today' => (int) $now->format('j'),
        ];

        return $this->view('dashboard/index', [
            'title' => 'Painel',
            'pageTitle' => 'Painel',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Painel', 'href' => null],
            ],
            'userName' => $name,
            'kpis' => $kpis,
            'chartSales' => $chartSales,
            'chartCashflow' => $chartCashflow,
            'chartLabels' => $chartLabels,
            'activities' => $activities,
            'alerts' => $alerts,
            'shortcuts' => $shortcuts,
            'supportTickets' => $tickets,
            'calendar' => $cal,
        ]);
    }

    /**
     * @return list<string>
     */
    private function last12MonthLabels(): array
    {
        $out = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = new \DateTimeImmutable('first day of this month');
            $d = $d->modify("-{$i} months");
            $out[] = $this->monthShortPt((int) $d->format('n')) . '/' . $d->format('y');
        }

        return $out;
    }

    private function monthShortPt(int $m): string
    {
        $map = [1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'];

        return $map[$m] ?? '';
    }

    private function monthNamePt(int $m): string
    {
        $map = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];

        return $map[$m] ?? '';
    }
}
