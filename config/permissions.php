<?php

declare(strict_types=1);

/**
 * Catálogo de permissões (slug único) — usado pelo seed e por database/sync_permissions.php.
 *
 * @return list<array{name: string, slug: string, module: string, action: string}>
 */
return [
    ['name' => 'Painel — visualizar', 'slug' => 'dashboard.view', 'module' => 'dashboard', 'action' => 'view'],

    ['name' => 'Usuários — listar', 'slug' => 'users.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Usuários — criar', 'slug' => 'users.create', 'module' => 'configuracoes', 'action' => 'create'],
    ['name' => 'Usuários — editar', 'slug' => 'users.edit', 'module' => 'configuracoes', 'action' => 'edit'],
    ['name' => 'Configurações — Usuários excluir', 'slug' => 'configuracoes.usuarios.delete', 'module' => 'configuracoes', 'action' => 'delete'],

    ['name' => 'Cadastros — Clientes', 'slug' => 'cadastros.clientes.view', 'module' => 'cadastros', 'action' => 'view'],
    ['name' => 'Cadastros — Clientes criar', 'slug' => 'cadastros.clientes.create', 'module' => 'cadastros', 'action' => 'create'],
    ['name' => 'Cadastros — Clientes editar', 'slug' => 'cadastros.clientes.edit', 'module' => 'cadastros', 'action' => 'edit'],
    ['name' => 'Cadastros — Clientes excluir', 'slug' => 'cadastros.clientes.delete', 'module' => 'cadastros', 'action' => 'delete'],

    ['name' => 'Cadastros — Fornecedores', 'slug' => 'cadastros.fornecedores.view', 'module' => 'cadastros', 'action' => 'view'],
    ['name' => 'Cadastros — Fornecedores criar', 'slug' => 'cadastros.fornecedores.create', 'module' => 'cadastros', 'action' => 'create'],
    ['name' => 'Cadastros — Fornecedores editar', 'slug' => 'cadastros.fornecedores.edit', 'module' => 'cadastros', 'action' => 'edit'],
    ['name' => 'Cadastros — Fornecedores excluir', 'slug' => 'cadastros.fornecedores.delete', 'module' => 'cadastros', 'action' => 'delete'],
    ['name' => 'Cadastros — Funcionários', 'slug' => 'cadastros.funcionarios.view', 'module' => 'cadastros', 'action' => 'view'],
    ['name' => 'Cadastros — Transportadoras', 'slug' => 'cadastros.transportadoras.view', 'module' => 'cadastros', 'action' => 'view'],
    ['name' => 'Cadastros — Opções auxiliares', 'slug' => 'cadastros.opcoes_auxiliares.view', 'module' => 'cadastros', 'action' => 'view'],

    ['name' => 'Produtos — Gerenciar', 'slug' => 'produtos.gerenciar.view', 'module' => 'produtos', 'action' => 'view'],
    ['name' => 'Produtos — Gerenciar criar', 'slug' => 'produtos.gerenciar.create', 'module' => 'produtos', 'action' => 'create'],
    ['name' => 'Produtos — Gerenciar editar', 'slug' => 'produtos.gerenciar.edit', 'module' => 'produtos', 'action' => 'edit'],
    ['name' => 'Produtos — Gerenciar excluir', 'slug' => 'produtos.gerenciar.delete', 'module' => 'produtos', 'action' => 'delete'],
    ['name' => 'Produtos — Valores de venda', 'slug' => 'produtos.valores_venda.view', 'module' => 'produtos', 'action' => 'view'],
    ['name' => 'Produtos — Etiquetas', 'slug' => 'produtos.etiquetas.view', 'module' => 'produtos', 'action' => 'view'],
    ['name' => 'Produtos — Opções auxiliares', 'slug' => 'produtos.opcoes_auxiliares.view', 'module' => 'produtos', 'action' => 'view'],

    ['name' => 'Serviços — Gerenciar', 'slug' => 'servicos.gerenciar.view', 'module' => 'servicos', 'action' => 'view'],
    ['name' => 'Serviços — Gerenciar criar', 'slug' => 'servicos.gerenciar.create', 'module' => 'servicos', 'action' => 'create'],
    ['name' => 'Serviços — Gerenciar editar', 'slug' => 'servicos.gerenciar.edit', 'module' => 'servicos', 'action' => 'edit'],
    ['name' => 'Serviços — Gerenciar excluir', 'slug' => 'servicos.gerenciar.delete', 'module' => 'servicos', 'action' => 'delete'],

    ['name' => 'Orçamentos — Produtos', 'slug' => 'orcamentos.produtos.view', 'module' => 'orcamentos', 'action' => 'view'],
    ['name' => 'Orçamentos — Serviços', 'slug' => 'orcamentos.servicos.view', 'module' => 'orcamentos', 'action' => 'view'],
    ['name' => 'Orçamentos — Opções auxiliares', 'slug' => 'orcamentos.opcoes_auxiliares.view', 'module' => 'orcamentos', 'action' => 'view'],

    ['name' => 'Ordens de serviço — Gerenciar', 'slug' => 'ordens_servico.gerenciar.view', 'module' => 'ordens_servico', 'action' => 'view'],
    ['name' => 'Ordens de serviço — Painel', 'slug' => 'ordens_servico.painel.view', 'module' => 'ordens_servico', 'action' => 'view'],
    ['name' => 'Ordens de serviço — Opções auxiliares', 'slug' => 'ordens_servico.opcoes_auxiliares.view', 'module' => 'ordens_servico', 'action' => 'view'],

    ['name' => 'Vendas — Produtos', 'slug' => 'vendas.produtos.view', 'module' => 'vendas', 'action' => 'view'],
    ['name' => 'Vendas — Balcão', 'slug' => 'vendas.balcao.view', 'module' => 'vendas', 'action' => 'view'],
    ['name' => 'Vendas — Serviços', 'slug' => 'vendas.servicos.view', 'module' => 'vendas', 'action' => 'view'],
    ['name' => 'Vendas — Opções auxiliares', 'slug' => 'vendas.opcoes_auxiliares.view', 'module' => 'vendas', 'action' => 'view'],

    ['name' => 'Estoque — Movimentações', 'slug' => 'estoque.movimentacoes.view', 'module' => 'estoque', 'action' => 'view'],
    ['name' => 'Estoque — Ajustes', 'slug' => 'estoque.ajustes.view', 'module' => 'estoque', 'action' => 'view'],
    ['name' => 'Estoque — Transferências', 'slug' => 'estoque.transferencias.view', 'module' => 'estoque', 'action' => 'view'],
    ['name' => 'Estoque — Cotações', 'slug' => 'estoque.cotacoes.view', 'module' => 'estoque', 'action' => 'view'],
    ['name' => 'Estoque — Compras', 'slug' => 'estoque.compras.view', 'module' => 'estoque', 'action' => 'view'],
    ['name' => 'Estoque — Trocas e devoluções', 'slug' => 'estoque.trocas_devolucoes.view', 'module' => 'estoque', 'action' => 'view'],
    ['name' => 'Estoque — Opções auxiliares', 'slug' => 'estoque.opcoes_auxiliares.view', 'module' => 'estoque', 'action' => 'view'],

    ['name' => 'Financeiro — Contas a pagar', 'slug' => 'financeiro.contas_pagar.view', 'module' => 'financeiro', 'action' => 'view'],
    ['name' => 'Financeiro — Contas a receber', 'slug' => 'financeiro.contas_receber.view', 'module' => 'financeiro', 'action' => 'view'],
    ['name' => 'Financeiro — DRE gerencial', 'slug' => 'financeiro.dre_gerencial.view', 'module' => 'financeiro', 'action' => 'view'],
    ['name' => 'Financeiro — Fluxo de caixa', 'slug' => 'financeiro.fluxo_caixa.view', 'module' => 'financeiro', 'action' => 'view'],
    ['name' => 'Financeiro — Boletos bancários', 'slug' => 'financeiro.boletos_bancarios.view', 'module' => 'financeiro', 'action' => 'view'],
    ['name' => 'Financeiro — Opções auxiliares', 'slug' => 'financeiro.opcoes_auxiliares.view', 'module' => 'financeiro', 'action' => 'view'],

    ['name' => 'Notas fiscais — Produtos', 'slug' => 'notas_fiscais.produtos.view', 'module' => 'notas_fiscais', 'action' => 'view'],
    ['name' => 'Notas fiscais — Serviços', 'slug' => 'notas_fiscais.servicos.view', 'module' => 'notas_fiscais', 'action' => 'view'],
    ['name' => 'Notas fiscais — Consumidor', 'slug' => 'notas_fiscais.consumidor.view', 'module' => 'notas_fiscais', 'action' => 'view'],
    ['name' => 'Notas fiscais — Compras', 'slug' => 'notas_fiscais.compras.view', 'module' => 'notas_fiscais', 'action' => 'view'],
    ['name' => 'Notas fiscais — Opções auxiliares', 'slug' => 'notas_fiscais.opcoes_auxiliares.view', 'module' => 'notas_fiscais', 'action' => 'view'],

    ['name' => 'Contratos — Serviços', 'slug' => 'contratos.servicos.view', 'module' => 'contratos', 'action' => 'view'],
    ['name' => 'Contratos — Locações', 'slug' => 'contratos.locacoes.view', 'module' => 'contratos', 'action' => 'view'],
    ['name' => 'Contratos — Assinaturas', 'slug' => 'contratos.assinaturas.view', 'module' => 'contratos', 'action' => 'view'],
    ['name' => 'Contratos — Opções auxiliares', 'slug' => 'contratos.opcoes_auxiliares.view', 'module' => 'contratos', 'action' => 'view'],

    ['name' => 'Atendimentos — Painel', 'slug' => 'atendimentos.painel.view', 'module' => 'atendimentos', 'action' => 'view'],
    ['name' => 'Atendimentos — Histórico', 'slug' => 'atendimentos.historico.view', 'module' => 'atendimentos', 'action' => 'view'],
    ['name' => 'Atendimentos — Status', 'slug' => 'atendimentos.status.view', 'module' => 'atendimentos', 'action' => 'view'],
    ['name' => 'Atendimentos — Opções auxiliares', 'slug' => 'atendimentos.opcoes_auxiliares.view', 'module' => 'atendimentos', 'action' => 'view'],

    ['name' => 'Relatórios — Cadastros', 'slug' => 'relatorios.cadastros.view', 'module' => 'relatorios', 'action' => 'view'],
    ['name' => 'Relatórios — Vendas', 'slug' => 'relatorios.vendas.view', 'module' => 'relatorios', 'action' => 'view'],
    ['name' => 'Relatórios — Ordens de serviço', 'slug' => 'relatorios.ordens_servico.view', 'module' => 'relatorios', 'action' => 'view'],
    ['name' => 'Relatórios — Estoque', 'slug' => 'relatorios.estoque.view', 'module' => 'relatorios', 'action' => 'view'],
    ['name' => 'Relatórios — Financeiro', 'slug' => 'relatorios.financeiro.view', 'module' => 'relatorios', 'action' => 'view'],
    ['name' => 'Relatórios — Contratos', 'slug' => 'relatorios.contratos.view', 'module' => 'relatorios', 'action' => 'view'],
    ['name' => 'Relatórios — Notas fiscais', 'slug' => 'relatorios.notas_fiscais.view', 'module' => 'relatorios', 'action' => 'view'],
    ['name' => 'Relatórios — Logs do sistema', 'slug' => 'relatorios.logs_sistema.view', 'module' => 'relatorios', 'action' => 'view'],

    ['name' => 'Configurações — Gerais', 'slug' => 'configuracoes.gerais.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Configurações — Meu plano', 'slug' => 'configuracoes.meu_plano.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Configurações — Tela de usuários', 'slug' => 'configuracoes.usuarios.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Configurações — Dados da empresa', 'slug' => 'configuracoes.dados_empresa.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Configurações — Marca da empresa', 'slug' => 'configuracoes.marca_empresa.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Configurações — Empresas / Lojas', 'slug' => 'configuracoes.empresas_lojas.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Configurações — Certificado digital', 'slug' => 'configuracoes.certificado_digital.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Configurações — Modelos de e-mails', 'slug' => 'configuracoes.modelos_email.view', 'module' => 'configuracoes', 'action' => 'view'],
    ['name' => 'Configurações — Avisos por e-mail', 'slug' => 'configuracoes.avisos_email.view', 'module' => 'configuracoes', 'action' => 'view'],
];
