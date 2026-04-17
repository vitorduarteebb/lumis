<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class VendasController extends Controller
{
    use RendersModulePlaceholder;

    public function produtos(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Vendas — Produtos',
            'pageTitle' => 'Vendas · Produtos',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Vendas', 'href' => null],
                ['label' => 'Produtos', 'href' => null],
            ],
            'description' => 'Pedidos e faturamento de mercadorias com reserva de estoque e integração fiscal.',
            'icon' => 'bi-cart3',
        ]);
    }

    public function balcao(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Vendas — Balcão',
            'pageTitle' => 'Balcão',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Vendas', 'href' => null],
                ['label' => 'Balcão', 'href' => null],
            ],
            'description' => 'PDV rápido: busca de produtos, pagamentos mistos e emissão de documentos.',
            'icon' => 'bi-shop',
        ]);
    }

    public function servicos(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Vendas — Serviços',
            'pageTitle' => 'Vendas · Serviços',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Vendas', 'href' => null],
                ['label' => 'Serviços', 'href' => null],
            ],
            'description' => 'Faturamento de serviços avulsos ou recorrentes com vínculo a contratos.',
            'icon' => 'bi-briefcase',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Vendas',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Vendas', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Tabelas de condição de pagamento, canais, comissões e políticas de desconto.',
            'icon' => 'bi-sliders',
        ]);
    }
}
