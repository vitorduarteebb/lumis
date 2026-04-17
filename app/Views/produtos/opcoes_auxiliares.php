<?php
declare(strict_types=1);

/** @var string $tab */
$tab = (string) ($tab ?? 'categorias');
$basePath = (string) ($basePath ?? '/produtos/opcoes-auxiliares');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
$categoriesRows = is_array($categoriesRows ?? null) ? $categoriesRows : [];
$brandsRows = is_array($brandsRows ?? null) ? $brandsRows : [];
$unitsRows = is_array($unitsRows ?? null) ? $unitsRows : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Produtos</div>
        <h2 class="h4 mb-1 text-white">Opções auxiliares</h2>
        <div class="text-secondary small">Categorias, marcas e unidades usadas no cadastro de produtos.</div>
    </div>
    <a class="btn btn-lumis-secondary btn-sm rounded-3" href="/produtos">Produtos</a>
</div>

<ul class="nav nav-pills gap-2 mb-3 flex-wrap">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'categorias' ? 'active' : '' ?>" href="/produtos/opcoes-auxiliares?tab=categorias">Categorias</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'marcas' ? 'active' : '' ?>" href="/produtos/opcoes-auxiliares?tab=marcas">Marcas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'unidades' ? 'active' : '' ?>" href="/produtos/opcoes-auxiliares?tab=unidades">Unidades</a>
    </li>
</ul>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') ?>">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 360px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Buscar…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<?php if ($tab === 'categorias'): ?>
    <?php if (can('produtos.opcoes_auxiliares.edit')): ?>
        <div class="lumis-form-section mb-4">
            <div class="lumis-form-section__title">Nova categoria</div>
            <form method="post" action="/produtos/opcoes-auxiliares" class="row g-2 align-items-end">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="_action" value="create_category">
                <input type="hidden" name="tab" value="categorias">
                <div class="col-md-4">
                    <label class="form-label lumis-label small mb-1" for="c_name">Nome</label>
                    <input type="text" class="form-control app-input" id="c_name" name="name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label lumis-label small mb-1" for="c_slug">Slug (opcional)</label>
                    <input type="text" class="form-control app-input" id="c_slug" name="slug" placeholder="auto se vazio">
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label small mb-1" for="c_st">Status</label>
                    <select class="form-select app-input" id="c_st" name="status">
                        <option value="1" selected>Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-3">Adicionar</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="lumis-table-wrap mb-2">
        <table class="table lumis-table mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <?php if (can('produtos.opcoes_auxiliares.edit')): ?><th class="text-end">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($categoriesRows === []): ?>
                    <tr><td colspan="4" class="text-secondary small py-4">Nenhuma categoria.</td></tr>
                <?php endif; ?>
                <?php foreach ($categoriesRows as $row): ?>
                    <?php $rid = (int) ($row['id'] ?? 0); $st = (int) ($row['status'] ?? 0); ?>
                    <tr>
                        <?php if (can('produtos.opcoes_auxiliares.edit')): ?>
                            <td colspan="4" class="p-0">
                                <div class="p-3 border-bottom border-secondary-subtle d-flex flex-wrap align-items-end gap-2 justify-content-between">
                                    <form method="post" action="/produtos/opcoes-auxiliares" class="flex-grow-1">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="_action" value="update_category">
                                        <input type="hidden" name="tab" value="categorias">
                                        <input type="hidden" name="id" value="<?= $rid ?>">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control form-control-sm app-input" name="name" value="<?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm app-input" name="slug" value="<?= htmlspecialchars((string) ($row['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-select form-select-sm app-input" name="status">
                                                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                                                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 text-md-end">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-3">Salvar</button>
                                            </div>
                                        </div>
                                    </form>
                                    <?php if ($st === 1): ?>
                                        <form method="post" action="/produtos/opcoes-auxiliares" class="pb-1" onsubmit="return confirm('Inativar esta categoria?');">
                                            <?= \App\Helpers\Csrf::field() ?>
                                            <input type="hidden" name="_action" value="delete_category">
                                            <input type="hidden" name="tab" value="categorias">
                                            <input type="hidden" name="id" value="<?= $rid ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">Inativar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php else: ?>
                            <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-secondary small"><?= htmlspecialchars((string) ($row['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $st === 1 ? '<span class="badge badge-lumis badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif ($tab === 'marcas'): ?>
    <?php if (can('produtos.opcoes_auxiliares.edit')): ?>
        <div class="lumis-form-section mb-4">
            <div class="lumis-form-section__title">Nova marca</div>
            <form method="post" action="/produtos/opcoes-auxiliares" class="row g-2 align-items-end">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="_action" value="create_brand">
                <input type="hidden" name="tab" value="marcas">
                <div class="col-md-5">
                    <label class="form-label lumis-label small mb-1" for="b_name">Nome</label>
                    <input type="text" class="form-control app-input" id="b_name" name="name" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label small mb-1" for="b_st">Status</label>
                    <select class="form-select app-input" id="b_st" name="status">
                        <option value="1" selected>Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-3">Adicionar</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="lumis-table-wrap mb-2">
        <table class="table lumis-table mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Status</th>
                    <?php if (can('produtos.opcoes_auxiliares.edit')): ?><th class="text-end">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($brandsRows === []): ?>
                    <tr><td colspan="3" class="text-secondary small py-4">Nenhuma marca.</td></tr>
                <?php endif; ?>
                <?php foreach ($brandsRows as $row): ?>
                    <?php $rid = (int) ($row['id'] ?? 0); $st = (int) ($row['status'] ?? 0); ?>
                    <tr>
                        <?php if (can('produtos.opcoes_auxiliares.edit')): ?>
                            <td colspan="3" class="p-0">
                                <div class="p-3 border-bottom border-secondary-subtle d-flex flex-wrap align-items-end gap-2 justify-content-between">
                                    <form method="post" action="/produtos/opcoes-auxiliares" class="flex-grow-1">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="_action" value="update_brand">
                                        <input type="hidden" name="tab" value="marcas">
                                        <input type="hidden" name="id" value="<?= $rid ?>">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm app-input" name="name" value="<?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-select form-select-sm app-input" name="status">
                                                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                                                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-3">Salvar</button>
                                            </div>
                                        </div>
                                    </form>
                                    <?php if ($st === 1): ?>
                                        <form method="post" action="/produtos/opcoes-auxiliares" class="pb-1" onsubmit="return confirm('Inativar esta marca?');">
                                            <?= \App\Helpers\Csrf::field() ?>
                                            <input type="hidden" name="_action" value="delete_brand">
                                            <input type="hidden" name="tab" value="marcas">
                                            <input type="hidden" name="id" value="<?= $rid ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">Inativar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php else: ?>
                            <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $st === 1 ? '<span class="badge badge-lumis badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <?php if (can('produtos.opcoes_auxiliares.edit')): ?>
        <div class="lumis-form-section mb-4">
            <div class="lumis-form-section__title">Nova unidade</div>
            <form method="post" action="/produtos/opcoes-auxiliares" class="row g-2 align-items-end">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="_action" value="create_unit">
                <input type="hidden" name="tab" value="unidades">
                <div class="col-md-4">
                    <label class="form-label lumis-label small mb-1" for="u_name">Nome</label>
                    <input type="text" class="form-control app-input" id="u_name" name="name" placeholder="Quilograma" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label small mb-1" for="u_abbr">Abreviação</label>
                    <input type="text" class="form-control app-input" id="u_abbr" name="abbreviation" placeholder="kg" required maxlength="16">
                </div>
                <div class="col-md-2">
                    <label class="form-label lumis-label small mb-1" for="u_st">Status</label>
                    <select class="form-select app-input" id="u_st" name="status">
                        <option value="1" selected>Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-3">Adicionar</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="lumis-table-wrap mb-2">
        <table class="table lumis-table mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Abreviação</th>
                    <th>Status</th>
                    <?php if (can('produtos.opcoes_auxiliares.edit')): ?><th class="text-end">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($unitsRows === []): ?>
                    <tr><td colspan="4" class="text-secondary small py-4">Nenhuma unidade.</td></tr>
                <?php endif; ?>
                <?php foreach ($unitsRows as $row): ?>
                    <?php $rid = (int) ($row['id'] ?? 0); $st = (int) ($row['status'] ?? 0); ?>
                    <tr>
                        <?php if (can('produtos.opcoes_auxiliares.edit')): ?>
                            <td colspan="4" class="p-0">
                                <div class="p-3 border-bottom border-secondary-subtle d-flex flex-wrap align-items-end gap-2 justify-content-between">
                                    <form method="post" action="/produtos/opcoes-auxiliares" class="flex-grow-1">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="_action" value="update_unit">
                                        <input type="hidden" name="tab" value="unidades">
                                        <input type="hidden" name="id" value="<?= $rid ?>">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control form-control-sm app-input" name="name" value="<?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control form-control-sm app-input" name="abbreviation" value="<?= htmlspecialchars((string) ($row['abbreviation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required maxlength="16">
                                            </div>
                                            <div class="col-md-2">
                                                <select class="form-select form-select-sm app-input" name="status">
                                                    <option value="1" <?= $st === 1 ? 'selected' : '' ?>>Ativo</option>
                                                    <option value="0" <?= $st === 0 ? 'selected' : '' ?>>Inativo</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 text-md-end">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-3">Salvar</button>
                                            </div>
                                        </div>
                                    </form>
                                    <?php if ($st === 1): ?>
                                        <form method="post" action="/produtos/opcoes-auxiliares" class="pb-1" onsubmit="return confirm('Inativar esta unidade?');">
                                            <?= \App\Helpers\Csrf::field() ?>
                                            <input type="hidden" name="_action" value="delete_unit">
                                            <input type="hidden" name="tab" value="unidades">
                                            <input type="hidden" name="id" value="<?= $rid ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">Inativar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php else: ?>
                            <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-secondary small"><?= htmlspecialchars((string) ($row['abbreviation'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $st === 1 ? '<span class="badge badge-lumis badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include base_path('app/Views/partials/pagination.php'); ?>
