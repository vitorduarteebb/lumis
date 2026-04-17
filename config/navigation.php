<?php

declare(strict_types=1);

/**
 * Menu lateral — URLs alinhadas a routes/module_routes.php e slugs em config/permissions.php.
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
            ['label' => 'Clientes', 'href' => '/cadastros/clientes'],
            ['label' => 'Fornecedores', 'href' => '/cadastros/fornecedores'],
            ['label' => 'Funcionários', 'href' => '/cadastros/funcionarios'],
            ['label' => 'Transportadoras', 'href' => '/cadastros/transportadoras'],
            ['label' => 'Opções auxiliares', 'href' => '/cadastros/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'produtos',
        'label' => 'Produtos',
        'icon' => 'bi-box-seam',
        'children' => [
            ['label' => 'Gerenciar produtos', 'href' => '/produtos'],
            ['label' => 'Valores de venda', 'href' => '/produtos/valores-venda'],
            ['label' => 'Etiquetas', 'href' => '/produtos/etiquetas'],
            ['label' => 'Opções auxiliares', 'href' => '/produtos/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'servicos',
        'label' => 'Serviços',
        'icon' => 'bi-wrench-adjustable',
        'href' => '/servicos',
        'match' => 'exact',
    ],
    [
        'key' => 'orcamentos',
        'label' => 'Orçamentos',
        'icon' => 'bi-file-earmark-text',
        'children' => [
            ['label' => 'Produtos', 'href' => '/orcamentos/produtos'],
            ['label' => 'Serviços', 'href' => '/orcamentos/servicos'],
            ['label' => 'Opções auxiliares', 'href' => '/orcamentos/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'ordens_servico',
        'label' => 'Ordens de Serviços',
        'icon' => 'bi-clipboard-check',
        'children' => [
            ['label' => 'Gerenciar O.S.', 'href' => '/ordens-servico'],
            ['label' => 'Painel', 'href' => '/ordens-servico/painel'],
            ['label' => 'Opções auxiliares', 'href' => '/ordens-servico/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'vendas',
        'label' => 'Vendas',
        'icon' => 'bi-cart3',
        'children' => [
            ['label' => 'Produtos', 'href' => '/vendas/produtos'],
            ['label' => 'Balcão', 'href' => '/vendas/balcao'],
            ['label' => 'Serviços', 'href' => '/vendas/servicos'],
            ['label' => 'Opções auxiliares', 'href' => '/vendas/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'estoque',
        'label' => 'Estoque',
        'icon' => 'bi-archive',
        'children' => [
            ['label' => 'Movimentações', 'href' => '/estoque/movimentacoes'],
            ['label' => 'Ajustes', 'href' => '/estoque/ajustes'],
            ['label' => 'Transferências', 'href' => '/estoque/transferencias'],
            ['label' => 'Cotações', 'href' => '/estoque/cotacoes'],
            ['label' => 'Compras', 'href' => '/estoque/compras'],
            ['label' => 'Trocas e devoluções', 'href' => '/estoque/trocas-devolucoes'],
            ['label' => 'Opções auxiliares', 'href' => '/estoque/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'financeiro',
        'label' => 'Financeiro',
        'icon' => 'bi-cash-stack',
        'children' => [
            ['label' => 'Contas a pagar', 'href' => '/financeiro/contas-pagar'],
            ['label' => 'Contas a receber', 'href' => '/financeiro/contas-receber'],
            ['label' => 'DRE gerencial', 'href' => '/financeiro/dre-gerencial'],
            ['label' => 'Fluxo de caixa', 'href' => '/financeiro/fluxo-caixa'],
            ['label' => 'Boletos bancários', 'href' => '/financeiro/boletos-bancarios'],
            ['label' => 'Opções auxiliares', 'href' => '/financeiro/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'notas_fiscais',
        'label' => 'Notas Fiscais',
        'icon' => 'bi-receipt',
        'children' => [
            ['label' => 'Notas de produtos', 'href' => '/notas-fiscais/produtos'],
            ['label' => 'Notas de serviços', 'href' => '/notas-fiscais/servicos'],
            ['label' => 'Notas do consumidor', 'href' => '/notas-fiscais/consumidor'],
            ['label' => 'Notas de compras', 'href' => '/notas-fiscais/compras'],
            ['label' => 'Opções auxiliares', 'href' => '/notas-fiscais/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'contratos',
        'label' => 'Contratos',
        'icon' => 'bi-file-earmark-ruled',
        'children' => [
            ['label' => 'Serviços', 'href' => '/contratos/servicos'],
            ['label' => 'Locações', 'href' => '/contratos/locacoes'],
            ['label' => 'Assinaturas', 'href' => '/contratos/assinaturas'],
            ['label' => 'Opções auxiliares', 'href' => '/contratos/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'atendimentos',
        'label' => 'Atendimentos',
        'icon' => 'bi-headset',
        'children' => [
            ['label' => 'Painel', 'href' => '/atendimentos/painel'],
            ['label' => 'Histórico', 'href' => '/atendimentos/historico'],
            ['label' => 'Status', 'href' => '/atendimentos/status'],
            ['label' => 'Opções auxiliares', 'href' => '/atendimentos/opcoes-auxiliares'],
        ],
    ],
    [
        'key' => 'relatorios',
        'label' => 'Relatórios',
        'icon' => 'bi-graph-up-arrow',
        'children' => [
            ['label' => 'Cadastros', 'href' => '/relatorios/cadastros'],
            ['label' => 'Vendas', 'href' => '/relatorios/vendas'],
            ['label' => 'Ordens de serviços', 'href' => '/relatorios/ordens-servico'],
            ['label' => 'Estoque', 'href' => '/relatorios/estoque'],
            ['label' => 'Financeiro', 'href' => '/relatorios/financeiro'],
            ['label' => 'Contratos', 'href' => '/relatorios/contratos'],
            ['label' => 'Notas fiscais', 'href' => '/relatorios/notas-fiscais'],
            ['label' => 'Logs do sistema', 'href' => '/relatorios/logs-sistema'],
        ],
    ],
    [
        'key' => 'configuracoes',
        'label' => 'Configurações',
        'icon' => 'bi-gear',
        'children' => [
            ['label' => 'Gerais', 'href' => '/configuracoes/gerais'],
            ['label' => 'Meu plano', 'href' => '/configuracoes/meu-plano'],
            ['label' => 'Usuários', 'href' => '/configuracoes/usuarios'],
            ['label' => 'Dados da empresa', 'href' => '/configuracoes/dados-empresa'],
            ['label' => 'Marca da empresa', 'href' => '/configuracoes/marca-empresa'],
            ['label' => 'Empresas / Lojas', 'href' => '/configuracoes/empresas-lojas'],
            ['label' => 'Certificado digital', 'href' => '/configuracoes/certificado-digital'],
            ['label' => 'Modelos de e-mails', 'href' => '/configuracoes/modelos-email'],
            ['label' => 'Avisos por e-mail', 'href' => '/configuracoes/avisos-email'],
        ],
    ],
];
