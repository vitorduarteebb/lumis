<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class EstoqueController extends Controller
{
    use RendersModulePlaceholder;

    public function movimentacoes(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Movimentações de estoque',
            'pageTitle' => 'Movimentações',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Movimentações', 'href' => null],
            ],
            'description' => 'Entradas, saídas, transferências e histórico por depósito e lote.',
            'icon' => 'bi-arrow-left-right',
        ]);
    }

    public function ajustes(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Ajustes de estoque',
            'pageTitle' => 'Ajustes',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Ajustes', 'href' => null],
            ],
            'description' => 'Inventário cíclico, acertos e justificativas auditáveis.',
            'icon' => 'bi-tools',
        ]);
    }

    public function transferencias(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Transferências',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Transferências', 'href' => null],
            ],
            'description' => 'Movimentação entre depósitos e lojas com rastreio de status.',
            'icon' => 'bi-truck',
        ]);
    }

    public function cotacoes(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Cotações de compra',
            'pageTitle' => 'Cotações',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Cotações', 'href' => null],
            ],
            'description' => 'Solicitações de preço a fornecedores e comparativo para decisão de compra.',
            'icon' => 'bi-chat-dots',
        ]);
    }

    public function compras(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Compras',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Compras', 'href' => null],
            ],
            'description' => 'Pedidos de compra, recebimento e conciliação com notas de entrada.',
            'icon' => 'bi-bag-check',
        ]);
    }

    public function trocasDevolucoes(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Trocas e devoluções',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Trocas e devoluções', 'href' => null],
            ],
            'description' => 'RMA, devoluções de venda e estorno com impacto fiscal e financeiro.',
            'icon' => 'bi-arrow-counterclockwise',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Estoque',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Estoque', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Depósitos, motivos de movimento, políticas de custo e parâmetros de reserva.',
            'icon' => 'bi-sliders',
        ]);
    }
}
