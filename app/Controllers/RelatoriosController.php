<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class RelatoriosController extends Controller
{
    use RendersModulePlaceholder;

    public function cadastros(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Relatórios — Cadastros',
            'pageTitle' => 'Relatórios · Cadastros',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => null],
                ['label' => 'Cadastros', 'href' => null],
            ],
            'description' => 'Exportações e análises de clientes, fornecedores e tabelas auxiliares.',
            'icon' => 'bi-graph-up-arrow',
        ]);
    }

    public function vendas(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Relatórios — Vendas',
            'pageTitle' => 'Relatórios · Vendas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => null],
                ['label' => 'Vendas', 'href' => null],
            ],
            'description' => 'Curva ABC, metas por vendedor e performance por canal.',
            'icon' => 'bi-graph-up-arrow',
        ]);
    }

    public function ordensServico(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Relatórios — Ordens de serviço',
            'pageTitle' => 'Relatórios · Ordens de serviço',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => null],
                ['label' => 'Ordens de serviço', 'href' => null],
            ],
            'description' => 'Produtividade, retrabalho e SLA por equipe.',
            'icon' => 'bi-graph-up-arrow',
        ]);
    }

    public function estoque(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Relatórios — Estoque',
            'pageTitle' => 'Relatórios · Estoque',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => null],
                ['label' => 'Estoque', 'href' => null],
            ],
            'description' => 'Posição, giro, ruptura e valorização.',
            'icon' => 'bi-graph-up-arrow',
        ]);
    }

    public function financeiro(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Relatórios — Financeiro',
            'pageTitle' => 'Relatórios · Financeiro',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => null],
                ['label' => 'Financeiro', 'href' => null],
            ],
            'description' => 'Inadimplência, fluxo projetado e comparativos de receita.',
            'icon' => 'bi-graph-up-arrow',
        ]);
    }

    public function contratos(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Relatórios — Contratos',
            'pageTitle' => 'Relatórios · Contratos',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => null],
                ['label' => 'Contratos', 'href' => null],
            ],
            'description' => 'MRR, churn e renovações.',
            'icon' => 'bi-graph-up-arrow',
        ]);
    }

    public function notasFiscais(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Relatórios — Notas fiscais',
            'pageTitle' => 'Relatórios · Notas fiscais',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => null],
                ['label' => 'Notas fiscais', 'href' => null],
            ],
            'description' => 'Volume por CFOP, status SEFAZ e auditoria de XML.',
            'icon' => 'bi-graph-up-arrow',
        ]);
    }

    public function logsSistema(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Logs do sistema',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Relatórios', 'href' => null],
                ['label' => 'Logs do sistema', 'href' => null],
            ],
            'description' => 'Trilha de auditoria, acessos e alterações críticas (integração com tabela audit_logs).',
            'icon' => 'bi-journal-text',
        ]);
    }
}
