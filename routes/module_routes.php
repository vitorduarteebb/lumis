<?php

declare(strict_types=1);

use App\Controllers\AtendimentosController;
use App\Controllers\ConfiguracoesController;
use App\Controllers\ContratosController;
use App\Controllers\EstoqueController;
use App\Controllers\FinanceiroController;
use App\Controllers\NotasFiscaisController;
use App\Controllers\OrcamentosController;
use App\Controllers\OrdensServicoController;
use App\Controllers\RelatoriosController;
use App\Controllers\VendasController;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\PermissionMiddleware;

/**
 * Rotas autenticadas dos módulos ERP (telas base / evolução futura).
 *
 * @return callable(Router): void
 */
return static function (Router $router): void {
    $m = static fn (string $permission, array $aliases = []): array => [
        AuthMiddleware::class,
        new PermissionMiddleware($permission, $aliases),
    ];

    /* Cadastros — CRUD em crud_routes.php */

    /* Produtos — rotas CRUD e submenus em crud_routes.php */

    /* Orçamentos */
    $router->get('/orcamentos/produtos', [OrcamentosController::class, 'produtos'], $m('orcamentos.produtos.view'));
    $router->get('/orcamentos/servicos', [OrcamentosController::class, 'servicos'], $m('orcamentos.servicos.view'));
    $router->get('/orcamentos/opcoes-auxiliares', [OrcamentosController::class, 'opcoesAuxiliares'], $m('orcamentos.opcoes_auxiliares.view'));

    /* Ordens de serviço */
    $router->get('/ordens-servico', [OrdensServicoController::class, 'index'], $m('ordens_servico.gerenciar.view'));
    $router->get('/ordens-servico/painel', [OrdensServicoController::class, 'painel'], $m('ordens_servico.painel.view'));
    $router->get('/ordens-servico/opcoes-auxiliares', [OrdensServicoController::class, 'opcoesAuxiliares'], $m('ordens_servico.opcoes_auxiliares.view'));

    /* Vendas */
    $router->get('/vendas/produtos', [VendasController::class, 'produtos'], $m('vendas.produtos.view'));
    $router->get('/vendas/balcao', [VendasController::class, 'balcao'], $m('vendas.balcao.view'));
    $router->get('/vendas/servicos', [VendasController::class, 'servicos'], $m('vendas.servicos.view'));
    $router->get('/vendas/opcoes-auxiliares', [VendasController::class, 'opcoesAuxiliares'], $m('vendas.opcoes_auxiliares.view'));

    /* Estoque */
    $router->get('/estoque/movimentacoes', [EstoqueController::class, 'movimentacoes'], $m('estoque.movimentacoes.view'));
    $router->get('/estoque/ajustes', [EstoqueController::class, 'ajustes'], $m('estoque.ajustes.view'));
    $router->get('/estoque/transferencias', [EstoqueController::class, 'transferencias'], $m('estoque.transferencias.view'));
    $router->get('/estoque/cotacoes', [EstoqueController::class, 'cotacoes'], $m('estoque.cotacoes.view'));
    $router->get('/estoque/compras', [EstoqueController::class, 'compras'], $m('estoque.compras.view'));
    $router->get('/estoque/trocas-devolucoes', [EstoqueController::class, 'trocasDevolucoes'], $m('estoque.trocas_devolucoes.view'));
    $router->get('/estoque/opcoes-auxiliares', [EstoqueController::class, 'opcoesAuxiliares'], $m('estoque.opcoes_auxiliares.view'));

    /* Financeiro */
    $router->get('/financeiro/contas-pagar', [FinanceiroController::class, 'contasPagar'], $m('financeiro.contas_pagar.view', ['finance.accounts_payable.view', 'finance.view']));
    $router->get('/financeiro/contas-receber', [FinanceiroController::class, 'contasReceber'], $m('financeiro.contas_receber.view', ['finance.accounts_receivable.view', 'finance.view']));
    $router->get('/financeiro/dre-gerencial', [FinanceiroController::class, 'dreGerencial'], $m('financeiro.dre_gerencial.view', ['finance.view']));
    $router->get('/financeiro/fluxo-caixa', [FinanceiroController::class, 'fluxoCaixa'], $m('financeiro.fluxo_caixa.view', ['finance.view']));
    $router->get('/financeiro/boletos-bancarios', [FinanceiroController::class, 'boletosBancarios'], $m('financeiro.boletos_bancarios.view', ['finance.view']));
    $router->get('/financeiro/opcoes-auxiliares', [FinanceiroController::class, 'opcoesAuxiliares'], $m('financeiro.opcoes_auxiliares.view', ['finance.view']));

    /* Notas fiscais */
    $router->get('/notas-fiscais/produtos', [NotasFiscaisController::class, 'produtos'], $m('notas_fiscais.produtos.view'));
    $router->get('/notas-fiscais/servicos', [NotasFiscaisController::class, 'servicos'], $m('notas_fiscais.servicos.view'));
    $router->get('/notas-fiscais/consumidor', [NotasFiscaisController::class, 'consumidor'], $m('notas_fiscais.consumidor.view'));
    $router->get('/notas-fiscais/compras', [NotasFiscaisController::class, 'compras'], $m('notas_fiscais.compras.view'));
    $router->get('/notas-fiscais/opcoes-auxiliares', [NotasFiscaisController::class, 'opcoesAuxiliares'], $m('notas_fiscais.opcoes_auxiliares.view'));

    /* Contratos */
    $router->get('/contratos/servicos', [ContratosController::class, 'servicos'], $m('contratos.servicos.view'));
    $router->get('/contratos/locacoes', [ContratosController::class, 'locacoes'], $m('contratos.locacoes.view'));
    $router->get('/contratos/assinaturas', [ContratosController::class, 'assinaturas'], $m('contratos.assinaturas.view'));
    $router->get('/contratos/opcoes-auxiliares', [ContratosController::class, 'opcoesAuxiliares'], $m('contratos.opcoes_auxiliares.view'));

    /* Atendimentos */
    $router->get('/atendimentos/painel', [AtendimentosController::class, 'painel'], $m('atendimentos.painel.view'));
    $router->get('/atendimentos/historico', [AtendimentosController::class, 'historico'], $m('atendimentos.historico.view'));
    $router->get('/atendimentos/status', [AtendimentosController::class, 'status'], $m('atendimentos.status.view'));
    $router->get('/atendimentos/opcoes-auxiliares', [AtendimentosController::class, 'opcoesAuxiliares'], $m('atendimentos.opcoes_auxiliares.view'));

    /* Relatórios */
    $router->get('/relatorios/cadastros', [RelatoriosController::class, 'cadastros'], $m('relatorios.cadastros.view'));
    $router->get('/relatorios/vendas', [RelatoriosController::class, 'vendas'], $m('relatorios.vendas.view'));
    $router->get('/relatorios/ordens-servico', [RelatoriosController::class, 'ordensServico'], $m('relatorios.ordens_servico.view'));
    $router->get('/relatorios/estoque', [RelatoriosController::class, 'estoque'], $m('relatorios.estoque.view'));
    $router->get('/relatorios/financeiro', [RelatoriosController::class, 'financeiro'], $m('relatorios.financeiro.view'));
    $router->get('/relatorios/contratos', [RelatoriosController::class, 'contratos'], $m('relatorios.contratos.view'));
    $router->get('/relatorios/notas-fiscais', [RelatoriosController::class, 'notasFiscais'], $m('relatorios.notas_fiscais.view'));
    $router->get('/relatorios/logs-sistema', [RelatoriosController::class, 'logsSistema'], $m('relatorios.logs_sistema.view'));

    /* Configurações */
    $router->get('/configuracoes/gerais', [ConfiguracoesController::class, 'gerais'], $m('configuracoes.gerais.view', ['settings.view']));
    $router->get('/configuracoes/meu-plano', [ConfiguracoesController::class, 'meuPlano'], $m('configuracoes.meu_plano.view'));
    $router->get('/configuracoes/dados-empresa', [ConfiguracoesController::class, 'dadosEmpresa'], $m('configuracoes.dados_empresa.view', ['settings.company.view']));
    $router->get('/configuracoes/marca-empresa', [ConfiguracoesController::class, 'marcaEmpresa'], $m('configuracoes.marca_empresa.view'));
    $router->get('/configuracoes/empresas-lojas', [ConfiguracoesController::class, 'empresasLojas'], $m('configuracoes.empresas_lojas.view'));
    $router->get('/configuracoes/certificado-digital', [ConfiguracoesController::class, 'certificadoDigital'], $m('configuracoes.certificado_digital.view'));
    $router->get('/configuracoes/modelos-email', [ConfiguracoesController::class, 'modelosEmail'], $m('configuracoes.modelos_email.view'));
    $router->get('/configuracoes/avisos-email', [ConfiguracoesController::class, 'avisosEmail'], $m('configuracoes.avisos_email.view'));
};
