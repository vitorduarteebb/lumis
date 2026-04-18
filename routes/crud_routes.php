<?php

declare(strict_types=1);

use App\Controllers\CadastrosLookupController;
use App\Controllers\EstoqueController;
use App\Controllers\EstoqueLookupController;
use App\Controllers\CarriersController;
use App\Controllers\ClientsController;
use App\Controllers\ConfiguracoesController;
use App\Controllers\EmployeesController;
use App\Controllers\FinanceiroController;
use App\Controllers\FinanceiroLookupController;
use App\Controllers\OrcamentosController;
use App\Controllers\OrcamentosLookupController;
use App\Controllers\OrdensServicoController;
use App\Controllers\OrdensServicoLookupController;
use App\Controllers\ProdutosController;
use App\Controllers\ServicosController;
use App\Controllers\SuppliersController;
use App\Controllers\UsersController;
use App\Controllers\VendasController;
use App\Controllers\VendasLookupController;
use App\Core\Router;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\PermissionMiddleware;

/**
 * Rotas CRUD reais (devem ser registradas antes de module_routes.php).
 * Ordem importa: rotas estáticas (ex.: /novo, /valores-venda) antes de /{id}.
 *
 * @return callable(Router): void
 */
return static function (Router $router): void {
    $m = static fn (string $permission, array $aliases = []): array => [
        AuthMiddleware::class,
        new PermissionMiddleware($permission, $aliases),
    ];

    /* ========== Configurações — Usuários ========== */
    $router->get('/configuracoes/usuarios/novo', [UsersController::class, 'create'], $m('configuracoes.usuarios.create', ['users.create']));
    $router->post('/configuracoes/usuarios', [UsersController::class, 'store'], $m('configuracoes.usuarios.create', ['users.create']));
    $router->get('/configuracoes/usuarios', [UsersController::class, 'index'], $m('configuracoes.usuarios.view', ['users.view']));
    $router->get('/configuracoes/usuarios/{id}/editar', [UsersController::class, 'edit'], $m('configuracoes.usuarios.edit', ['users.edit']));
    $router->post('/configuracoes/usuarios/{id}/excluir', [UsersController::class, 'destroy'], $m('configuracoes.usuarios.delete', ['users.delete']));
    $router->post('/configuracoes/usuarios/{id}', [UsersController::class, 'update'], $m('configuracoes.usuarios.edit', ['users.edit']));
    $router->get('/configuracoes/usuarios/{id}', [UsersController::class, 'show'], $m('configuracoes.usuarios.view', ['users.view']));

    /* ========== Cadastros — Clientes ========== */
    $router->get('/cadastros/clientes/novo', [ClientsController::class, 'create'], $m('cadastros.clientes.create', ['clients.create']));
    $router->post('/cadastros/clientes', [ClientsController::class, 'store'], $m('cadastros.clientes.create', ['clients.create']));
    $router->get('/cadastros/clientes', [ClientsController::class, 'index'], $m('cadastros.clientes.view', ['clients.view']));
    $router->get('/cadastros/clientes/{id}/editar', [ClientsController::class, 'edit'], $m('cadastros.clientes.edit', ['clients.edit']));
    $router->post('/cadastros/clientes/{id}/excluir', [ClientsController::class, 'destroy'], $m('cadastros.clientes.delete', ['clients.delete']));
    $router->post('/cadastros/clientes/{id}', [ClientsController::class, 'update'], $m('cadastros.clientes.edit', ['clients.edit']));
    $router->get('/cadastros/clientes/{id}', [ClientsController::class, 'show'], $m('cadastros.clientes.view', ['clients.view']));

    /* ========== Cadastros — Fornecedores ========== */
    $router->get('/cadastros/fornecedores/novo', [SuppliersController::class, 'create'], $m('cadastros.fornecedores.create', ['suppliers.create']));
    $router->post('/cadastros/fornecedores', [SuppliersController::class, 'store'], $m('cadastros.fornecedores.create', ['suppliers.create']));
    $router->get('/cadastros/fornecedores', [SuppliersController::class, 'index'], $m('cadastros.fornecedores.view', ['suppliers.view']));
    $router->get('/cadastros/fornecedores/{id}/editar', [SuppliersController::class, 'edit'], $m('cadastros.fornecedores.edit', ['suppliers.edit']));
    $router->post('/cadastros/fornecedores/{id}/excluir', [SuppliersController::class, 'destroy'], $m('cadastros.fornecedores.delete', ['suppliers.delete']));
    $router->post('/cadastros/fornecedores/{id}', [SuppliersController::class, 'update'], $m('cadastros.fornecedores.edit', ['suppliers.edit']));
    $router->get('/cadastros/fornecedores/{id}', [SuppliersController::class, 'show'], $m('cadastros.fornecedores.view', ['suppliers.view']));

    /* ========== Cadastros — Funcionários ========== */
    $router->get('/cadastros/funcionarios/novo', [EmployeesController::class, 'create'], $m('cadastros.funcionarios.create', ['employees.create']));
    $router->post('/cadastros/funcionarios', [EmployeesController::class, 'store'], $m('cadastros.funcionarios.create', ['employees.create']));
    $router->get('/cadastros/funcionarios', [EmployeesController::class, 'index'], $m('cadastros.funcionarios.view', ['employees.view']));
    $router->get('/cadastros/funcionarios/{id}/editar', [EmployeesController::class, 'edit'], $m('cadastros.funcionarios.edit', ['employees.edit']));
    $router->post('/cadastros/funcionarios/{id}/excluir', [EmployeesController::class, 'destroy'], $m('cadastros.funcionarios.delete', ['employees.delete']));
    $router->post('/cadastros/funcionarios/{id}', [EmployeesController::class, 'update'], $m('cadastros.funcionarios.edit', ['employees.edit']));
    $router->get('/cadastros/funcionarios/{id}', [EmployeesController::class, 'show'], $m('cadastros.funcionarios.view', ['employees.view']));

    /* ========== Cadastros — Transportadoras ========== */
    $router->get('/cadastros/transportadoras/novo', [CarriersController::class, 'create'], $m('cadastros.transportadoras.create', ['carriers.create']));
    $router->post('/cadastros/transportadoras', [CarriersController::class, 'store'], $m('cadastros.transportadoras.create', ['carriers.create']));
    $router->get('/cadastros/transportadoras', [CarriersController::class, 'index'], $m('cadastros.transportadoras.view', ['carriers.view']));
    $router->get('/cadastros/transportadoras/{id}/editar', [CarriersController::class, 'edit'], $m('cadastros.transportadoras.edit', ['carriers.edit']));
    $router->post('/cadastros/transportadoras/{id}/excluir', [CarriersController::class, 'destroy'], $m('cadastros.transportadoras.delete', ['carriers.delete']));
    $router->post('/cadastros/transportadoras/{id}', [CarriersController::class, 'update'], $m('cadastros.transportadoras.edit', ['carriers.edit']));
    $router->get('/cadastros/transportadoras/{id}', [CarriersController::class, 'show'], $m('cadastros.transportadoras.view', ['carriers.view']));

    /* ========== Cadastros — Opções auxiliares (lookups) ========== */
    $router->get('/cadastros/opcoes-auxiliares/novo', [CadastrosLookupController::class, 'create'], $m('cadastros.opcoes_auxiliares.create'));
    $router->post('/cadastros/opcoes-auxiliares', [CadastrosLookupController::class, 'store'], $m('cadastros.opcoes_auxiliares.create'));
    $router->get('/cadastros/opcoes-auxiliares', [CadastrosLookupController::class, 'index'], $m('cadastros.opcoes_auxiliares.view'));
    $router->get('/cadastros/opcoes-auxiliares/{id}/editar', [CadastrosLookupController::class, 'edit'], $m('cadastros.opcoes_auxiliares.edit'));
    $router->post('/cadastros/opcoes-auxiliares/{id}/excluir', [CadastrosLookupController::class, 'destroy'], $m('cadastros.opcoes_auxiliares.delete'));
    $router->post('/cadastros/opcoes-auxiliares/{id}', [CadastrosLookupController::class, 'update'], $m('cadastros.opcoes_auxiliares.edit'));

    /* ========== Produtos — submenus estáticos (antes de /{id}) ========== */
    $router->post('/produtos/valores-venda', [ProdutosController::class, 'valoresVendaSave'], $m('produtos.valores_venda.edit'));
    $router->post('/produtos/etiquetas/imprimir', [ProdutosController::class, 'etiquetasImprimir'], $m('produtos.etiquetas.print'));
    $router->post('/produtos/opcoes-auxiliares', [ProdutosController::class, 'opcoesCatalogoPost'], $m('produtos.opcoes_auxiliares.edit'));
    $router->get('/produtos/valores-venda', [ProdutosController::class, 'valoresVenda'], $m('produtos.valores_venda.view'));
    $router->get('/produtos/etiquetas', [ProdutosController::class, 'etiquetas'], $m('produtos.etiquetas.view'));
    $router->get('/produtos/opcoes-auxiliares', [ProdutosController::class, 'opcoesAuxiliares'], $m('produtos.opcoes_auxiliares.view'));

    /* ========== Produtos — CRUD ========== */
    $router->get('/produtos/novo', [ProdutosController::class, 'create'], $m('produtos.gerenciar.create', ['products.create']));
    $router->post('/produtos', [ProdutosController::class, 'store'], $m('produtos.gerenciar.create', ['products.create']));
    $router->get('/produtos', [ProdutosController::class, 'index'], $m('produtos.gerenciar.view', ['products.view']));
    $router->get('/produtos/{id}/editar', [ProdutosController::class, 'edit'], $m('produtos.gerenciar.edit', ['products.edit']));
    $router->post('/produtos/{id}/excluir', [ProdutosController::class, 'destroy'], $m('produtos.gerenciar.delete', ['products.delete']));
    $router->post('/produtos/{id}', [ProdutosController::class, 'update'], $m('produtos.gerenciar.edit', ['products.edit']));
    $router->get('/produtos/{id}', [ProdutosController::class, 'show'], $m('produtos.gerenciar.view', ['products.view']));

    /* ========== Configurações — POST e rotas estáticas (antes de module_routes) ========== */
    $router->post('/configuracoes/gerais', [ConfiguracoesController::class, 'geraisSave'], $m('configuracoes.gerais.edit'));
    $router->post('/configuracoes/meu-plano', [ConfiguracoesController::class, 'meuPlanoSave'], $m('configuracoes.meu_plano.edit'));
    $router->post('/configuracoes/dados-empresa', [ConfiguracoesController::class, 'dadosEmpresaSave'], $m('configuracoes.dados_empresa.edit'));
    $router->post('/configuracoes/marca-empresa', [ConfiguracoesController::class, 'marcaEmpresaSave'], $m('configuracoes.marca_empresa.edit'));
    $router->post('/configuracoes/empresas-lojas', [ConfiguracoesController::class, 'empresasLojasStore'], $m('configuracoes.empresas_lojas.edit'));
    $router->post('/configuracoes/certificado-digital', [ConfiguracoesController::class, 'certificadoDigitalSave'], $m('configuracoes.certificado_digital.edit'));
    $router->post('/configuracoes/certificado-digital/excluir', [ConfiguracoesController::class, 'certificadoDigitalDelete'], $m('configuracoes.certificado_digital.edit'));
    $router->post('/configuracoes/avisos-email', [ConfiguracoesController::class, 'avisosEmailSave'], $m('configuracoes.avisos_email.edit'));
    $router->get('/configuracoes/modelos-email/novo', [ConfiguracoesController::class, 'modelosEmailNovo'], $m('configuracoes.modelos_email.edit'));
    $router->post('/configuracoes/modelos-email', [ConfiguracoesController::class, 'modelosEmailStore'], $m('configuracoes.modelos_email.edit'));
    $router->get('/configuracoes/modelos-email/{id}/editar', [ConfiguracoesController::class, 'modelosEmailEditar'], $m('configuracoes.modelos_email.edit'));
    $router->post('/configuracoes/modelos-email/{id}/excluir', [ConfiguracoesController::class, 'modelosEmailDestroy'], $m('configuracoes.modelos_email.edit'));
    $router->post('/configuracoes/modelos-email/{id}', [ConfiguracoesController::class, 'modelosEmailUpdate'], $m('configuracoes.modelos_email.edit'));

    /* ========== Serviços — CRUD ========== */
    $router->get('/servicos/novo', [ServicosController::class, 'create'], $m('servicos.gerenciar.create', ['services.create']));
    $router->post('/servicos', [ServicosController::class, 'store'], $m('servicos.gerenciar.create', ['services.create']));
    $router->get('/servicos', [ServicosController::class, 'index'], $m('servicos.gerenciar.view', ['services.view']));
    $router->get('/servicos/{id}/editar', [ServicosController::class, 'edit'], $m('servicos.gerenciar.edit', ['services.edit']));
    $router->post('/servicos/{id}/excluir', [ServicosController::class, 'destroy'], $m('servicos.gerenciar.delete', ['services.delete']));
    $router->post('/servicos/{id}', [ServicosController::class, 'update'], $m('servicos.gerenciar.edit', ['services.edit']));
    $router->get('/servicos/{id}', [ServicosController::class, 'show'], $m('servicos.gerenciar.view', ['services.view']));

    /* ========== Financeiro — POST e opções auxiliares (antes de module_routes) ========== */
    $router->post('/financeiro/contas-pagar', [FinanceiroController::class, 'contasPagarPost'], $m('financeiro.contas_pagar.view', ['finance.accounts_payable.view', 'finance.view']));
    $router->post('/financeiro/contas-receber', [FinanceiroController::class, 'contasReceberPost'], $m('financeiro.contas_receber.view', ['finance.accounts_receivable.view', 'finance.view']));
    $router->post('/financeiro/boletos-bancarios', [FinanceiroController::class, 'boletosPost'], $m('financeiro.boletos_bancarios.view', ['finance.view']));

    $router->get('/financeiro/opcoes-auxiliares/novo', [FinanceiroLookupController::class, 'create'], $m('financeiro.opcoes_auxiliares.view', ['finance.view']));
    $router->post('/financeiro/opcoes-auxiliares', [FinanceiroLookupController::class, 'store'], $m('financeiro.opcoes_auxiliares.view', ['finance.view']));
    $router->get('/financeiro/opcoes-auxiliares/{id}/editar', [FinanceiroLookupController::class, 'edit'], $m('financeiro.opcoes_auxiliares.view', ['finance.view']));
    $router->post('/financeiro/opcoes-auxiliares/{id}/excluir', [FinanceiroLookupController::class, 'destroy'], $m('financeiro.opcoes_auxiliares.view', ['finance.view']));
    $router->post('/financeiro/opcoes-auxiliares/{id}', [FinanceiroLookupController::class, 'update'], $m('financeiro.opcoes_auxiliares.view', ['finance.view']));
    $router->get('/financeiro/opcoes-auxiliares', [FinanceiroLookupController::class, 'index'], $m('financeiro.opcoes_auxiliares.view', ['finance.view']));

    /* ========== Orçamentos — produtos ========== */
    $router->get('/orcamentos/produtos/novo', [OrcamentosController::class, 'produtosNovo'], $m('orcamentos.produtos.create'));
    $router->post('/orcamentos/produtos', [OrcamentosController::class, 'produtosStore'], $m('orcamentos.produtos.create'));
    $router->post('/orcamentos/produtos/{id}/excluir', [OrcamentosController::class, 'produtosDestroy'], $m('orcamentos.produtos.delete'));
    $router->post('/orcamentos/produtos/{id}/duplicar', [OrcamentosController::class, 'produtosDuplicate'], $m('orcamentos.produtos.create'));
    $router->post('/orcamentos/produtos/{id}/converter-venda', [OrcamentosController::class, 'produtosConverterVenda'], $m('orcamentos.produtos.edit'));
    $router->post('/orcamentos/produtos/{id}', [OrcamentosController::class, 'produtosUpdate'], $m('orcamentos.produtos.edit'));
    $router->get('/orcamentos/produtos/{id}/pdf', [OrcamentosController::class, 'produtosPdf'], $m('orcamentos.produtos.view'));
    $router->get('/orcamentos/produtos/{id}/editar', [OrcamentosController::class, 'produtosEdit'], $m('orcamentos.produtos.edit'));
    $router->get('/orcamentos/produtos/{id}', [OrcamentosController::class, 'produtosShow'], $m('orcamentos.produtos.view'));
    $router->get('/orcamentos/produtos', [OrcamentosController::class, 'produtos'], $m('orcamentos.produtos.view'));

    /* ========== Orçamentos — serviços ========== */
    $router->get('/orcamentos/servicos/novo', [OrcamentosController::class, 'servicosNovo'], $m('orcamentos.servicos.create'));
    $router->post('/orcamentos/servicos', [OrcamentosController::class, 'servicosStore'], $m('orcamentos.servicos.create'));
    $router->post('/orcamentos/servicos/{id}/excluir', [OrcamentosController::class, 'servicosDestroy'], $m('orcamentos.servicos.delete'));
    $router->post('/orcamentos/servicos/{id}/duplicar', [OrcamentosController::class, 'servicosDuplicate'], $m('orcamentos.servicos.create'));
    $router->post('/orcamentos/servicos/{id}/converter-os', [OrcamentosController::class, 'servicosConverterOs'], $m('orcamentos.servicos.edit'));
    $router->post('/orcamentos/servicos/{id}', [OrcamentosController::class, 'servicosUpdate'], $m('orcamentos.servicos.edit'));
    $router->get('/orcamentos/servicos/{id}/pdf', [OrcamentosController::class, 'servicosPdf'], $m('orcamentos.servicos.view'));
    $router->get('/orcamentos/servicos/{id}/editar', [OrcamentosController::class, 'servicosEdit'], $m('orcamentos.servicos.edit'));
    $router->get('/orcamentos/servicos/{id}', [OrcamentosController::class, 'servicosShow'], $m('orcamentos.servicos.view'));
    $router->get('/orcamentos/servicos', [OrcamentosController::class, 'servicos'], $m('orcamentos.servicos.view'));

    /* ========== Orçamentos — opções auxiliares ========== */
    $router->get('/orcamentos/opcoes-auxiliares/novo', [OrcamentosLookupController::class, 'create'], $m('orcamentos.opcoes_auxiliares.create'));
    $router->post('/orcamentos/opcoes-auxiliares', [OrcamentosLookupController::class, 'store'], $m('orcamentos.opcoes_auxiliares.create'));
    $router->get('/orcamentos/opcoes-auxiliares/{id}/editar', [OrcamentosLookupController::class, 'edit'], $m('orcamentos.opcoes_auxiliares.edit'));
    $router->post('/orcamentos/opcoes-auxiliares/{id}/excluir', [OrcamentosLookupController::class, 'destroy'], $m('orcamentos.opcoes_auxiliares.delete'));
    $router->post('/orcamentos/opcoes-auxiliares/{id}', [OrcamentosLookupController::class, 'update'], $m('orcamentos.opcoes_auxiliares.edit'));
    $router->get('/orcamentos/opcoes-auxiliares', [OrcamentosLookupController::class, 'index'], $m('orcamentos.opcoes_auxiliares.view'));

    /* ========== Ordens de serviço (estáticos antes de /{id}) ========== */
    $router->get('/ordens-servico/painel', [OrdensServicoController::class, 'painel'], $m('ordens_servico.painel.view'));
    $router->get('/ordens-servico/opcoes-auxiliares/novo', [OrdensServicoLookupController::class, 'create'], $m('ordens_servico.opcoes_auxiliares.create'));
    $router->post('/ordens-servico/opcoes-auxiliares', [OrdensServicoLookupController::class, 'store'], $m('ordens_servico.opcoes_auxiliares.create'));
    $router->get('/ordens-servico/opcoes-auxiliares/{id}/editar', [OrdensServicoLookupController::class, 'edit'], $m('ordens_servico.opcoes_auxiliares.edit'));
    $router->post('/ordens-servico/opcoes-auxiliares/{id}/excluir', [OrdensServicoLookupController::class, 'destroy'], $m('ordens_servico.opcoes_auxiliares.delete'));
    $router->post('/ordens-servico/opcoes-auxiliares/{id}', [OrdensServicoLookupController::class, 'update'], $m('ordens_servico.opcoes_auxiliares.edit'));
    $router->get('/ordens-servico/opcoes-auxiliares', [OrdensServicoLookupController::class, 'index'], $m('ordens_servico.opcoes_auxiliares.view'));
    $router->get('/ordens-servico/novo', [OrdensServicoController::class, 'novo'], $m('ordens_servico.create'));
    $router->post('/ordens-servico', [OrdensServicoController::class, 'store'], $m('ordens_servico.create'));
    $router->post('/ordens-servico/{id}/excluir', [OrdensServicoController::class, 'destroy'], $m('ordens_servico.delete'));
    $router->post('/ordens-servico/{id}', [OrdensServicoController::class, 'update'], $m('ordens_servico.edit'));
    $router->get('/ordens-servico/{id}/pdf', [OrdensServicoController::class, 'pdf'], $m('ordens_servico.view', ['ordens_servico.gerenciar.view']));
    $router->get('/ordens-servico/{id}/editar', [OrdensServicoController::class, 'edit'], $m('ordens_servico.edit'));
    $router->get('/ordens-servico/{id}', [OrdensServicoController::class, 'show'], $m('ordens_servico.view', ['ordens_servico.gerenciar.view']));
    $router->get('/ordens-servico', [OrdensServicoController::class, 'index'], $m('ordens_servico.view', ['ordens_servico.gerenciar.view']));

    /* ========== Vendas ========== */
    $router->post('/vendas/produtos/{id}/finalizar', [VendasController::class, 'produtosFinalizar'], $m('vendas.produtos.edit', ['vendas.produtos.view']));
    $router->post('/vendas/produtos/{id}/cancelar', [VendasController::class, 'produtosCancelar'], $m('vendas.produtos.edit', ['vendas.produtos.view']));
    $router->get('/vendas/produtos/novo', [VendasController::class, 'produtosNovo'], $m('vendas.produtos.create', ['vendas.produtos.view']));
    $router->post('/vendas/produtos', [VendasController::class, 'produtosStore'], $m('vendas.produtos.create', ['vendas.produtos.view']));
    $router->get('/vendas/produtos/{id}/pdf', [VendasController::class, 'produtosPdf'], $m('vendas.produtos.view'));
    $router->get('/vendas/produtos/{id}/editar', [VendasController::class, 'produtosEdit'], $m('vendas.produtos.edit', ['vendas.produtos.view']));
    $router->post('/vendas/produtos/{id}', [VendasController::class, 'produtosUpdate'], $m('vendas.produtos.edit', ['vendas.produtos.view']));
    $router->get('/vendas/produtos/{id}', [VendasController::class, 'produtosShow'], $m('vendas.produtos.view'));
    $router->get('/vendas/produtos', [VendasController::class, 'produtos'], $m('vendas.produtos.view'));

    $router->get('/vendas/balcao', [VendasController::class, 'balcao'], $m('vendas.balcao.view'));
    $router->post('/vendas/balcao', [VendasController::class, 'balcao'], $m('vendas.balcao.create', ['vendas.balcao.view']));

    $router->post('/vendas/servicos/{id}/finalizar', [VendasController::class, 'servicosFinalizar'], $m('vendas.servicos.edit', ['vendas.servicos.view']));
    $router->post('/vendas/servicos/{id}/cancelar', [VendasController::class, 'servicosCancelar'], $m('vendas.servicos.edit', ['vendas.servicos.view']));
    $router->get('/vendas/servicos/novo', [VendasController::class, 'servicosNovo'], $m('vendas.servicos.create', ['vendas.servicos.view']));
    $router->post('/vendas/servicos', [VendasController::class, 'servicosStore'], $m('vendas.servicos.create', ['vendas.servicos.view']));
    $router->get('/vendas/servicos/{id}/pdf', [VendasController::class, 'servicosPdf'], $m('vendas.servicos.view'));
    $router->get('/vendas/servicos/{id}/editar', [VendasController::class, 'servicosEdit'], $m('vendas.servicos.edit', ['vendas.servicos.view']));
    $router->post('/vendas/servicos/{id}', [VendasController::class, 'servicosUpdate'], $m('vendas.servicos.edit', ['vendas.servicos.view']));
    $router->get('/vendas/servicos/{id}', [VendasController::class, 'servicosShow'], $m('vendas.servicos.view'));
    $router->get('/vendas/servicos', [VendasController::class, 'servicos'], $m('vendas.servicos.view'));

    $router->get('/vendas/opcoes-auxiliares/novo', [VendasLookupController::class, 'create'], $m('vendas.opcoes_auxiliares.create', ['vendas.opcoes_auxiliares.view']));
    $router->post('/vendas/opcoes-auxiliares', [VendasLookupController::class, 'store'], $m('vendas.opcoes_auxiliares.create', ['vendas.opcoes_auxiliares.view']));
    $router->get('/vendas/opcoes-auxiliares/{id}/editar', [VendasLookupController::class, 'edit'], $m('vendas.opcoes_auxiliares.edit', ['vendas.opcoes_auxiliares.view']));
    $router->post('/vendas/opcoes-auxiliares/{id}/excluir', [VendasLookupController::class, 'destroy'], $m('vendas.opcoes_auxiliares.delete', ['vendas.opcoes_auxiliares.view']));
    $router->post('/vendas/opcoes-auxiliares/{id}', [VendasLookupController::class, 'update'], $m('vendas.opcoes_auxiliares.edit', ['vendas.opcoes_auxiliares.view']));
    $router->get('/vendas/opcoes-auxiliares', [VendasLookupController::class, 'index'], $m('vendas.opcoes_auxiliares.view'));

    /* ========== Estoque ========== */
    $router->get('/estoque/movimentacoes/{id}', [EstoqueController::class, 'movimentacaoShow'], $m('estoque.movimentacoes.view'));
    $router->get('/estoque/movimentacoes', [EstoqueController::class, 'movimentacoes'], $m('estoque.movimentacoes.view'));

    $router->get('/estoque/ajustes/novo', [EstoqueController::class, 'ajustesNovo'], $m('estoque.ajustes.create', ['estoque.ajustes.view']));
    $router->post('/estoque/ajustes', [EstoqueController::class, 'ajustesStore'], $m('estoque.ajustes.create', ['estoque.ajustes.view']));
    $router->get('/estoque/ajustes/{id}', [EstoqueController::class, 'ajusteShow'], $m('estoque.ajustes.view'));
    $router->get('/estoque/ajustes', [EstoqueController::class, 'ajustes'], $m('estoque.ajustes.view'));

    $router->post('/estoque/transferencias/{id}/concluir', [EstoqueController::class, 'transferenciaConcluir'], $m('estoque.transferencias.edit', ['estoque.transferencias.view']));
    $router->get('/estoque/transferencias/novo', [EstoqueController::class, 'transferenciasNovo'], $m('estoque.transferencias.create', ['estoque.transferencias.view']));
    $router->post('/estoque/transferencias', [EstoqueController::class, 'transferenciasStore'], $m('estoque.transferencias.create', ['estoque.transferencias.view']));
    $router->get('/estoque/transferencias/{id}', [EstoqueController::class, 'transferenciaShow'], $m('estoque.transferencias.view'));
    $router->get('/estoque/transferencias', [EstoqueController::class, 'transferencias'], $m('estoque.transferencias.view'));

    $router->get('/estoque/cotacoes/novo', [EstoqueController::class, 'cotacoesNovo'], $m('estoque.cotacoes.create', ['estoque.cotacoes.view']));
    $router->post('/estoque/cotacoes', [EstoqueController::class, 'cotacoesStore'], $m('estoque.cotacoes.create', ['estoque.cotacoes.view']));
    $router->post('/estoque/cotacoes/{id}/excluir', [EstoqueController::class, 'cotacaoCancelar'], $m('estoque.cotacoes.delete', ['estoque.cotacoes.view']));
    $router->get('/estoque/cotacoes/{id}/editar', [EstoqueController::class, 'cotacaoEditar'], $m('estoque.cotacoes.edit', ['estoque.cotacoes.view']));
    $router->post('/estoque/cotacoes/{id}', [EstoqueController::class, 'cotacaoUpdate'], $m('estoque.cotacoes.edit', ['estoque.cotacoes.view']));
    $router->get('/estoque/cotacoes/{id}', [EstoqueController::class, 'cotacaoShow'], $m('estoque.cotacoes.view'));
    $router->get('/estoque/cotacoes', [EstoqueController::class, 'cotacoes'], $m('estoque.cotacoes.view'));

    $router->post('/estoque/compras/{id}/finalizar', [EstoqueController::class, 'compraFinalizar'], $m('estoque.compras.edit', ['estoque.compras.view']));
    $router->get('/estoque/compras/novo', [EstoqueController::class, 'comprasNovo'], $m('estoque.compras.create', ['estoque.compras.view']));
    $router->post('/estoque/compras', [EstoqueController::class, 'comprasStore'], $m('estoque.compras.create', ['estoque.compras.view']));
    $router->get('/estoque/compras/{id}/editar', [EstoqueController::class, 'compraEditar'], $m('estoque.compras.edit', ['estoque.compras.view']));
    $router->post('/estoque/compras/{id}', [EstoqueController::class, 'compraUpdate'], $m('estoque.compras.edit', ['estoque.compras.view']));
    $router->get('/estoque/compras/{id}', [EstoqueController::class, 'compraShow'], $m('estoque.compras.view'));
    $router->get('/estoque/compras', [EstoqueController::class, 'compras'], $m('estoque.compras.view'));

    $router->get('/estoque/trocas-devolucoes/novo', [EstoqueController::class, 'trocasNovo'], $m('estoque.trocas_devolucoes.create', ['estoque.trocas_devolucoes.view']));
    $router->post('/estoque/trocas-devolucoes', [EstoqueController::class, 'trocasStore'], $m('estoque.trocas_devolucoes.create', ['estoque.trocas_devolucoes.view']));
    $router->get('/estoque/trocas-devolucoes/{id}', [EstoqueController::class, 'trocaShow'], $m('estoque.trocas_devolucoes.view'));
    $router->get('/estoque/trocas-devolucoes', [EstoqueController::class, 'trocasDevolucoes'], $m('estoque.trocas_devolucoes.view'));

    $router->get('/estoque/opcoes-auxiliares/novo', [EstoqueLookupController::class, 'create'], $m('estoque.opcoes_auxiliares.create', ['estoque.opcoes_auxiliares.view']));
    $router->post('/estoque/opcoes-auxiliares', [EstoqueLookupController::class, 'store'], $m('estoque.opcoes_auxiliares.create', ['estoque.opcoes_auxiliares.view']));
    $router->get('/estoque/opcoes-auxiliares/{id}/editar', [EstoqueLookupController::class, 'edit'], $m('estoque.opcoes_auxiliares.edit', ['estoque.opcoes_auxiliares.view']));
    $router->post('/estoque/opcoes-auxiliares/{id}/excluir', [EstoqueLookupController::class, 'destroy'], $m('estoque.opcoes_auxiliares.delete', ['estoque.opcoes_auxiliares.view']));
    $router->post('/estoque/opcoes-auxiliares/{id}', [EstoqueLookupController::class, 'update'], $m('estoque.opcoes_auxiliares.edit', ['estoque.opcoes_auxiliares.view']));
    $router->get('/estoque/opcoes-auxiliares', [EstoqueLookupController::class, 'index'], $m('estoque.opcoes_auxiliares.view'));
};
