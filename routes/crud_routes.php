<?php

declare(strict_types=1);

use App\Controllers\CadastrosLookupController;
use App\Controllers\CarriersController;
use App\Controllers\ClientsController;
use App\Controllers\ConfiguracoesController;
use App\Controllers\EmployeesController;
use App\Controllers\ProdutosController;
use App\Controllers\ServicosController;
use App\Controllers\SuppliersController;
use App\Controllers\UsersController;
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
};
