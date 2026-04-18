<?php

declare(strict_types=1);

use App\Controllers\AtendimentosController;
use App\Controllers\ConfiguracoesController;
use App\Controllers\FinanceiroController;
use App\Controllers\RelatoriosController;
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

    /* Orçamentos — rotas reais em crud_routes.php */

    /* Ordens de serviço — rotas reais em crud_routes.php */

    /* Vendas e Estoque — rotas operacionais em crud_routes.php */

    /* Financeiro */
    $router->get('/financeiro/contas-pagar', [FinanceiroController::class, 'contasPagar'], $m('financeiro.contas_pagar.view', ['finance.accounts_payable.view', 'finance.view']));
    $router->get('/financeiro/contas-receber', [FinanceiroController::class, 'contasReceber'], $m('financeiro.contas_receber.view', ['finance.accounts_receivable.view', 'finance.view']));
    $router->get('/financeiro/dre-gerencial', [FinanceiroController::class, 'dreGerencial'], $m('financeiro.dre_gerencial.view', ['finance.view']));
    $router->get('/financeiro/fluxo-caixa', [FinanceiroController::class, 'fluxoCaixa'], $m('financeiro.fluxo_caixa.view', ['finance.view']));
    $router->get('/financeiro/boletos-bancarios', [FinanceiroController::class, 'boletosBancarios'], $m('financeiro.boletos_bancarios.view', ['finance.view']));

    /* Notas fiscais e contratos — CRUD em crud_routes.php */

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
