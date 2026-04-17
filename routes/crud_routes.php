<?php

declare(strict_types=1);

use App\Controllers\ClientsController;
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

    /* ========== Produtos — submenus estáticos (antes de /{id}) ========== */
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

    /* ========== Serviços — CRUD ========== */
    $router->get('/servicos/novo', [ServicosController::class, 'create'], $m('servicos.gerenciar.create', ['services.create']));
    $router->post('/servicos', [ServicosController::class, 'store'], $m('servicos.gerenciar.create', ['services.create']));
    $router->get('/servicos', [ServicosController::class, 'index'], $m('servicos.gerenciar.view', ['services.view']));
    $router->get('/servicos/{id}/editar', [ServicosController::class, 'edit'], $m('servicos.gerenciar.edit', ['services.edit']));
    $router->post('/servicos/{id}/excluir', [ServicosController::class, 'destroy'], $m('servicos.gerenciar.delete', ['services.delete']));
    $router->post('/servicos/{id}', [ServicosController::class, 'update'], $m('servicos.gerenciar.edit', ['services.edit']));
    $router->get('/servicos/{id}', [ServicosController::class, 'show'], $m('servicos.gerenciar.view', ['services.view']));
};
