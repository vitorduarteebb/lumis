<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class OrdensServicoController extends Controller
{
    use RendersModulePlaceholder;

    public function index(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Gerenciar ordens de serviço',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => null],
                ['label' => 'Gerenciar O.S.', 'href' => null],
            ],
            'description' => 'Abertura, acompanhamento e encerramento de O.S. com técnicos, peças e tempos.',
            'icon' => 'bi-clipboard-check',
            'primaryAction' => [
                'label' => 'Nova O.S.',
                'href' => '#',
                'disabled' => true,
                'hint' => 'Fluxo completo será plugado nesta rota.',
            ],
        ]);
    }

    public function painel(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Painel de ordens de serviço',
            'pageTitle' => 'Painel',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => null],
                ['label' => 'Painel', 'href' => null],
            ],
            'description' => 'Visão operacional: filas, SLA, mapa de calor e indicadores por equipe.',
            'icon' => 'bi-grid-1x2',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Ordens de serviço',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Ordens de serviço', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Tipos de O.S., prioridades, checklists e motivos de encerramento.',
            'icon' => 'bi-sliders',
        ]);
    }
}
