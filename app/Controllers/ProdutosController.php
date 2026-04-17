<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class ProdutosController extends Controller
{
    use RendersModulePlaceholder;

    public function index(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Gerenciar produtos',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Gerenciar produtos', 'href' => null],
            ],
            'description' => 'Catálogo de SKUs, variações, NCM/CEST, unidades e políticas de venda. Base para estoque e fiscal.',
            'icon' => 'bi-box-seam',
            'primaryAction' => [
                'label' => 'Novo produto',
                'href' => '#',
                'disabled' => true,
                'hint' => 'CRUD de produtos será implementado mantendo esta URL.',
            ],
        ]);
    }

    public function valoresVenda(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Valores de venda',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Valores de venda', 'href' => null],
            ],
            'description' => 'Tabelas de preço, regras por canal, descontos e vigência — integradas ao catálogo e ao PDV.',
            'icon' => 'bi-currency-dollar',
        ]);
    }

    public function etiquetas(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Etiquetas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Etiquetas', 'href' => null],
            ],
            'description' => 'Layouts de etiquetas, impressão em lote e códigos de barras para gôndola e expedição.',
            'icon' => 'bi-printer',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Produtos',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Produtos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Marcas, famílias, unidades de medida e demais tabelas de apoio ao catálogo.',
            'icon' => 'bi-sliders',
        ]);
    }
}
