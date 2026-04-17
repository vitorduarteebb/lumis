<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class FinanceiroController extends Controller
{
    use RendersModulePlaceholder;

    public function contasPagar(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Contas a pagar',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Contas a pagar', 'href' => null],
            ],
            'description' => 'Títulos, aprovações, agendamento de pagamento e conciliação bancária.',
            'icon' => 'bi-credit-card-2-front',
            'primaryAction' => [
                'label' => 'Novo título',
                'href' => '#',
                'disabled' => true,
                'hint' => 'Integração com compras e boletos será conectada aqui.',
            ],
        ]);
    }

    public function contasReceber(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Contas a receber',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Contas a receber', 'href' => null],
            ],
            'description' => 'Cobrança, boletos, PIX e inadimplência com visão por cliente e carteira.',
            'icon' => 'bi-wallet2',
        ]);
    }

    public function dreGerencial(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'DRE gerencial',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'DRE gerencial', 'href' => null],
            ],
            'description' => 'Demonstração do resultado com centros de custo e comparativos mensais.',
            'icon' => 'bi-bar-chart-line',
        ]);
    }

    public function fluxoCaixa(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Fluxo de caixa',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Fluxo de caixa', 'href' => null],
            ],
            'description' => 'Projetado vs. realizado, saldos por conta e cenários de tesouraria.',
            'icon' => 'bi-graph-up-arrow',
        ]);
    }

    public function boletosBancarios(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Boletos bancários',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Boletos bancários', 'href' => null],
            ],
            'description' => 'Registro, remessa, retorno e baixa automática de boletos.',
            'icon' => 'bi-upc-scan',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Financeiro',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Financeiro', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Plano de contas, centros de custo, bancos e parâmetros de integração.',
            'icon' => 'bi-sliders',
        ]);
    }
}
