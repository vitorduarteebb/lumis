<?php

declare(strict_types=1);

/**
 * Menu lateral principal — hrefs com # estão reservados para módulos futuros.
 *
 * @return list<array<string, mixed>>
 */
return [
    [
        'key' => 'dashboard',
        'label' => 'Dashboard',
        'icon' => 'bi-speedometer2',
        'href' => '/dashboard',
        'match' => 'exact',
    ],
    [
        'key' => 'cadastros',
        'label' => 'Cadastros',
        'icon' => 'bi-people',
        'children' => [
            ['label' => 'Clientes', 'href' => '#', 'disabled' => true],
            ['label' => 'Fornecedores', 'href' => '#', 'disabled' => true],
            ['label' => 'Funcionários', 'href' => '#', 'disabled' => true],
            ['label' => 'Transportadoras', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'produtos',
        'label' => 'Produtos',
        'icon' => 'bi-box-seam',
        'children' => [
            ['label' => 'Gerenciar produtos', 'href' => '#', 'disabled' => true],
            ['label' => 'Valores de venda', 'href' => '#', 'disabled' => true],
            ['label' => 'Etiquetas', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'servicos',
        'label' => 'Serviços',
        'icon' => 'bi-wrench-adjustable',
        'href' => '#',
        'disabled' => true,
    ],
    [
        'key' => 'orcamentos',
        'label' => 'Orçamentos',
        'icon' => 'bi-file-earmark-text',
        'children' => [
            ['label' => 'Produtos', 'href' => '#', 'disabled' => true],
            ['label' => 'Serviços', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'ordens_servico',
        'label' => 'Ordens de Serviços',
        'icon' => 'bi-clipboard-check',
        'children' => [
            ['label' => 'Gerenciar O.S.', 'href' => '#', 'disabled' => true],
            ['label' => 'Painel', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'vendas',
        'label' => 'Vendas',
        'icon' => 'bi-cart3',
        'children' => [
            ['label' => 'Produtos', 'href' => '#', 'disabled' => true],
            ['label' => 'Balcão', 'href' => '#', 'disabled' => true],
            ['label' => 'Serviços', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'estoque',
        'label' => 'Estoque',
        'icon' => 'bi-archive',
        'children' => [
            ['label' => 'Movimentações', 'href' => '#', 'disabled' => true],
            ['label' => 'Ajustes', 'href' => '#', 'disabled' => true],
            ['label' => 'Transferências', 'href' => '#', 'disabled' => true],
            ['label' => 'Cotações', 'href' => '#', 'disabled' => true],
            ['label' => 'Compras', 'href' => '#', 'disabled' => true],
            ['label' => 'Trocas e devoluções', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'financeiro',
        'label' => 'Financeiro',
        'icon' => 'bi-cash-stack',
        'children' => [
            ['label' => 'Contas a pagar', 'href' => '#', 'disabled' => true],
            ['label' => 'Contas a receber', 'href' => '#', 'disabled' => true],
            ['label' => 'DRE gerencial', 'href' => '#', 'disabled' => true],
            ['label' => 'Fluxo de caixa', 'href' => '#', 'disabled' => true],
            ['label' => 'Boletos bancários', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'notas_fiscais',
        'label' => 'Notas Fiscais',
        'icon' => 'bi-receipt',
        'children' => [
            ['label' => 'Notas de produtos', 'href' => '#', 'disabled' => true],
            ['label' => 'Notas de serviços', 'href' => '#', 'disabled' => true],
            ['label' => 'Notas do consumidor', 'href' => '#', 'disabled' => true],
            ['label' => 'Notas de compras', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'contratos',
        'label' => 'Contratos',
        'icon' => 'bi-file-earmark-ruled',
        'children' => [
            ['label' => 'Serviços', 'href' => '#', 'disabled' => true],
            ['label' => 'Locações', 'href' => '#', 'disabled' => true],
            ['label' => 'Assinaturas', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'atendimentos',
        'label' => 'Atendimentos',
        'icon' => 'bi-headset',
        'children' => [
            ['label' => 'Painel', 'href' => '#', 'disabled' => true],
            ['label' => 'Histórico', 'href' => '#', 'disabled' => true],
            ['label' => 'Status', 'href' => '#', 'disabled' => true],
            ['label' => 'Opções auxiliares', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'relatorios',
        'label' => 'Relatórios',
        'icon' => 'bi-graph-up-arrow',
        'children' => [
            ['label' => 'Cadastros', 'href' => '#', 'disabled' => true],
            ['label' => 'Vendas', 'href' => '#', 'disabled' => true],
            ['label' => 'Ordens de serviços', 'href' => '#', 'disabled' => true],
            ['label' => 'Estoque', 'href' => '#', 'disabled' => true],
            ['label' => 'Financeiro', 'href' => '#', 'disabled' => true],
            ['label' => 'Contratos', 'href' => '#', 'disabled' => true],
            ['label' => 'Notas fiscais', 'href' => '#', 'disabled' => true],
            ['label' => 'Logs do sistema', 'href' => '#', 'disabled' => true],
        ],
    ],
    [
        'key' => 'configuracoes',
        'label' => 'Configurações',
        'icon' => 'bi-gear',
        'children' => [
            ['label' => 'Gerais', 'href' => '#', 'disabled' => true],
            ['label' => 'Meu plano', 'href' => '#', 'disabled' => true],
            ['label' => 'Usuários', 'href' => '#', 'disabled' => true],
            ['label' => 'Dados da empresa', 'href' => '#', 'disabled' => true],
            ['label' => 'Marca da empresa', 'href' => '#', 'disabled' => true],
            ['label' => 'Empresas / Lojas', 'href' => '#', 'disabled' => true],
            ['label' => 'Certificado digital', 'href' => '#', 'disabled' => true],
            ['label' => 'Modelos de e-mails', 'href' => '#', 'disabled' => true],
            ['label' => 'Avisos por e-mail', 'href' => '#', 'disabled' => true],
        ],
    ],
];
