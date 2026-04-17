<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class AtendimentosController extends Controller
{
    use RendersModulePlaceholder;

    public function painel(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Painel de atendimentos',
            'pageTitle' => 'Painel',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Atendimentos', 'href' => null],
                ['label' => 'Painel', 'href' => null],
            ],
            'description' => 'Filas, tempos de resposta e distribuição entre agentes.',
            'icon' => 'bi-headset',
        ]);
    }

    public function historico(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Histórico de atendimentos',
            'pageTitle' => 'Histórico',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Atendimentos', 'href' => null],
                ['label' => 'Histórico', 'href' => null],
            ],
            'description' => 'Consulta de tickets, interações e anexos por cliente.',
            'icon' => 'bi-clock-history',
        ]);
    }

    public function status(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Status de atendimento',
            'pageTitle' => 'Status',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Atendimentos', 'href' => null],
                ['label' => 'Status', 'href' => null],
            ],
            'description' => 'Configuração de pipelines, SLA e motivos de pausa.',
            'icon' => 'bi-kanban',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Atendimentos',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Atendimentos', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Canais, horários de funcionamento e templates de resposta.',
            'icon' => 'bi-sliders',
        ]);
    }
}
