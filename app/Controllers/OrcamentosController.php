<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class OrcamentosController extends Controller
{
    use RendersModulePlaceholder;

    public function produtos(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Orçamentos — Produtos',
            'pageTitle' => 'Orçamentos · Produtos',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => 'Produtos', 'href' => null],
            ],
            'description' => 'Orçamentos com itens de produto, composição de kits e políticas comerciais.',
            'icon' => 'bi-file-earmark-text',
        ]);
    }

    public function servicos(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Orçamentos — Serviços',
            'pageTitle' => 'Orçamentos · Serviços',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => 'Serviços', 'href' => null],
            ],
            'description' => 'Propostas focadas em serviços, prazos e escopo — conversão em O.S. ou contrato.',
            'icon' => 'bi-file-earmark-text',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Orçamentos',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Orçamentos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Status de orçamento, motivos de perda, templates e parâmetros de validade.',
            'icon' => 'bi-sliders',
        ]);
    }
}
