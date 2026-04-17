<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class NotasFiscaisController extends Controller
{
    use RendersModulePlaceholder;

    public function produtos(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Notas fiscais — Produtos',
            'pageTitle' => 'NF-e · Produtos',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => 'Notas de produtos', 'href' => null],
            ],
            'description' => 'Emissão e gestão de NF-e de mercadorias, com status SEFAZ e armazenamento XML.',
            'icon' => 'bi-receipt',
        ]);
    }

    public function servicos(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Notas fiscais — Serviços',
            'pageTitle' => 'NFS-e · Serviços',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => 'Notas de serviços', 'href' => null],
            ],
            'description' => 'NFS-e municipal, retenções e integração com ordens de serviço.',
            'icon' => 'bi-receipt-cutoff',
        ]);
    }

    public function consumidor(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Notas do consumidor',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => 'Notas do consumidor', 'href' => null],
            ],
            'description' => 'NFC-e e documentos de venda ao consumidor com contingência.',
            'icon' => 'bi-person-vcard',
        ]);
    }

    public function compras(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Notas de compras',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => 'Notas de compras', 'href' => null],
            ],
            'description' => 'Manifestação, importação de XML e conferência com pedidos de compra.',
            'icon' => 'bi-cloud-download',
        ]);
    }

    public function opcoesAuxiliares(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Opções auxiliares — Notas fiscais',
            'pageTitle' => 'Opções auxiliares',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Notas fiscais', 'href' => null],
                ['label' => 'Opções auxiliares', 'href' => null],
            ],
            'description' => 'CFOP, CST, regras fiscais por UF e parâmetros de numeração.',
            'icon' => 'bi-sliders',
        ]);
    }
}
