<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\RendersModulePlaceholder;
use App\Core\Controller;
use App\Core\Request;

final class ServicosController extends Controller
{
    use RendersModulePlaceholder;

    public function index(Request $request): string
    {
        return $this->modulePlaceholder([
            'title' => 'Gerenciar serviços',
            'breadcrumbs' => [
                ['label' => 'Início', 'href' => '/dashboard'],
                ['label' => 'Serviços', 'href' => null],
            ],
            'description' => 'Cadastro de serviços, CNAE/LC 116, composição de custos e vínculo com ordens de serviço e NFS-e.',
            'icon' => 'bi-wrench-adjustable',
            'primaryAction' => [
                'label' => 'Novo serviço',
                'href' => '#',
                'disabled' => true,
                'hint' => 'Listagem e formulário usarão esta rota como ponto de entrada.',
            ],
        ]);
    }
}
