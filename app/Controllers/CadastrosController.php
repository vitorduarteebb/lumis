<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class CadastrosController extends Controller
{
    use RendersModulePlaceholder;

    public function clientes(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Clientes',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Clientes', 'href' => null],
            ],
            'description' => 'Centralize pessoas físicas e jurídicas, condições comerciais, contatos e histórico. Esta área receberá listagem, cadastro e integrações com vendas e fiscal.',
            'icon' => 'bi-people',
            'primaryAction' => [
                'label' => 'Novo cliente',
                'href' => '#',
                'disabled' => true,
                'hint' => 'Formulário completo será habilitado na próxima etapa do CRUD.',
            ],
        ]);
    }

    public function fornecedores(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Fornecedores',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Fornecedores', 'href' => null],
            ],
            'description' => 'Gerencie fornecedores, prazos, categorias e vínculos com compras e estoque.',
            'icon' => 'bi-truck',
            'primaryAction' => [
                'label' => 'Novo fornecedor',
                'href' => '#',
                'disabled' => true,
                'hint' => 'Disponível quando o fluxo de compras for conectado.',
            ],
        ]);
    }

    public function funcionarios(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Funcionários',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Funcionários', 'href' => null],
            ],
            'description' => 'Cadastro de colaboradores, cargos, vínculos com usuários do sistema e permissões operacionais.',
            'icon' => 'bi-person-badge',
            'primaryAction' => [
                'label' => 'Novo funcionário',
                'href' => '#',
                'disabled' => true,
                'hint' => 'Integração com RH e ponto pode ser adicionada depois.',
            ],
        ]);
    }

    public function transportadoras(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Transportadoras',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Transportadoras', 'href' => null],
            ],
            'description' => 'Transportadoras e tabelas de frete para expedição, rastreio e integração com notas fiscais.',
            'icon' => 'bi-box-seam',
            'primaryAction' => [
                'label' => 'Nova transportadora',
                'href' => '#',
                'disabled' => true,
                'hint' => 'Campos de CNPJ, IE e serviços de cotação virão aqui.',
            ],
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Cadastros',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Cadastros', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'Tabelas de apoio: categorias, tags, origens de contato e demais listas usadas nos cadastros principais.',
            'icon' => 'bi-sliders',
        ]);
    }
}
