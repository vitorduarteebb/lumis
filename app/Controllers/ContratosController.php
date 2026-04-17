<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class ContratosController extends Controller
{
    use RendersModulePlaceholder;

    public function servicos(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Contratos — Serviços',
            'pageTitle' => 'Contratos · Serviços',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Serviços', 'href' => null],
            ],
            'description' => 'Contratos de prestação de serviço com faturamento recorrente e renovação.',
            'icon' => 'bi-file-earmark-ruled',
        ]);
    }

    public function locacoes(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Contratos — Locações',
            'pageTitle' => 'Contratos · Locações',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Locações', 'href' => null],
            ],
            'description' => 'Gestão de locações de bens, reajustes e vencimentos.',
            'icon' => 'bi-building',
        ]);
    }

    public function assinaturas(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Contratos — Assinaturas',
            'pageTitle' => 'Contratos · Assinaturas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Assinaturas', 'href' => null],
            ],
            'description' => 'Planos SaaS, cobrança recorrente e ciclo de vida do assinante.',
            'icon' => 'bi-arrow-repeat',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Contratos',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Contratos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Modelos de contrato, cláusulas padrão e aprovações.',
            'icon' => 'bi-sliders',
        ]);
    }
}
