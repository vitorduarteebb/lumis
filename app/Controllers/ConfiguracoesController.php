<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class ConfiguracoesController extends Controller
{
    use RendersModulePlaceholder;

    public function gerais(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Configurações gerais',
            'pageTitle' => 'Gerais',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Gerais', 'href' => null],
            ],
            'description' => 'Fuso horário, idioma, formato de números e comportamento global do sistema.',
            'icon' => 'bi-gear',
        ]);
    }

    public function meuPlano(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Meu plano',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Meu plano', 'href' => null],
            ],
            'description' => 'Limites do SaaS, faturamento e upgrade de plano.',
            'icon' => 'bi-stars',
        ]);
    }

    public function usuarios(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Usuários',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Usuários', 'href' => null],
            ],
            'description' => 'Gestão de contas, perfis e convites. Evolua para CRUD completo mantendo permissões `users.*`.',
            'icon' => 'bi-person-gear',
            'primaryAction' => [
                'label' => 'Convidar usuário',
                'href' => '#',
                'disabled' => true,
                'hint' => 'Use permissões users.create e users.edit nos formulários futuros.',
            ],
        ]);
    }

    public function dadosEmpresa(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Dados da empresa',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Dados da empresa', 'href' => null],
            ],
            'description' => 'Razão social, CNPJ, IE, endereço e regime tributário exibidos em documentos.',
            'icon' => 'bi-building',
        ]);
    }

    public function marcaEmpresa(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Marca da empresa',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Marca da empresa', 'href' => null],
            ],
            'description' => 'Logotipo, cores e identidade aplicada a PDFs, e-mails e telas.',
            'icon' => 'bi-palette',
        ]);
    }

    public function empresasLojas(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Empresas / Lojas',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Empresas / Lojas', 'href' => null],
            ],
            'description' => 'Matriz, filiais e pontos de venda com escopo de estoque e fiscal.',
            'icon' => 'bi-shop-window',
        ]);
    }

    public function certificadoDigital(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Certificado digital',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Certificado digital', 'href' => null],
            ],
            'description' => 'Upload A1/A3, validade e vínculo com emissão de NF-e.',
            'icon' => 'bi-shield-lock',
        ]);
    }

    public function modelosEmail(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Modelos de e-mails',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Modelos de e-mails', 'href' => null],
            ],
            'description' => 'Templates transacionais com variáveis dinâmicas (pedido, boleto, NF-e).',
            'icon' => 'bi-envelope-paper',
        ]);
    }

    public function avisosEmail(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Avisos por e-mail',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Configurações', 'href' => null],
                ['label' => 'Avisos por e-mail', 'href' => null],
            ],
            'description' => 'Alertas de estoque, financeiro e SLA enviados por e-mail.',
            'icon' => 'bi-bell',
        ]);
    }
}
