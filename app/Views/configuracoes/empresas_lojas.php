<?php
declare(strict_types=1);

/** @var array<string, mixed> $company */
/** @var list<array<string, mixed>> $stores */
$company = is_array($company ?? null) ? $company : [];
$stores = is_array($stores ?? null) ? $stores : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Empresas / Lojas</h2>
        <div class="text-secondary small">Matriz e pontos de venda vinculados à empresa atual.</div>
    </div>
</div>

<div class="lumis-form-section mb-4">
    <div class="lumis-form-section__title">Empresa atual</div>
    <p class="text-white mb-0"><strong><?= htmlspecialchars((string) ($company['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></p>
    <p class="text-secondary small mb-0">Slug: <?= htmlspecialchars((string) ($company['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
</div>

<?php if (can('configuracoes.empresas_lojas.edit')): ?>
    <div class="lumis-form-section mb-4">
        <div class="lumis-form-section__title">Nova loja / filial</div>
        <form method="post" action="/configuracoes/empresas-lojas" class="row g-2 align-items-end">
            <?= \App\Helpers\Csrf::field() ?>
            <input type="hidden" name="_action" value="create_store">
            <div class="col-md-4">
                <label class="form-label lumis-label small mb-1" for="store_name">Nome</label>
                <input type="text" class="form-control app-input" id="store_name" name="store_name" required>
            </div>
            <div class="col-md-3">
                <label class="form-label lumis-label small mb-1" for="store_slug">Slug (opcional)</label>
                <input type="text" class="form-control app-input" id="store_slug" name="store_slug" placeholder="auto">
            </div>
            <div class="col-md-2">
                <label class="form-label lumis-label small mb-1" for="store_status">Status</label>
                <select class="form-select app-input" id="store_status" name="store_status">
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
                <th>Loja</th>
                <th>Slug</th>
                <th>Status</th>
                <?php if (can('configuracoes.empresas_lojas.edit')): ?><th class="text-end">Ações</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($stores === []): ?>
                <tr><td colspan="4" class="text-secondary small py-4">Nenhuma loja cadastrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($stores as $st): ?>
                <?php
                $sid = (int) ($st['id'] ?? 0);
                $sst = (int) ($st['status'] ?? 0);
                ?>
                <tr>
                    <?php if (can('configuracoes.empresas_lojas.edit')): ?>
                        <td colspan="4" class="p-0">
                            <div class="p-3 border-bottom border-secondary-subtle d-flex flex-wrap align-items-end gap-2 justify-content-between">
                                <form method="post" action="/configuracoes/empresas-lojas" class="flex-grow-1">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <input type="hidden" name="_action" value="update_store">
                                    <input type="hidden" name="store_id" value="<?= $sid ?>">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control form-control-sm app-input" name="name" value="<?= htmlspecialchars((string) ($st['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control form-control-sm app-input" name="slug" value="<?= htmlspecialchars((string) ($st['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select form-select-sm app-input" name="status">
                                                <option value="1" <?= $sst === 1 ? 'selected' : '' ?>>Ativo</option>
                                                <option value="0" <?= $sst === 0 ? 'selected' : '' ?>>Inativo</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 text-md-end">
                                            <button type="submit" class="btn btn-sm btn-primary rounded-3">Salvar</button>
                                        </div>
                                    </div>
                                </form>
                                <?php if ($sst === 1): ?>
                                    <form method="post" action="/configuracoes/empresas-lojas" class="pb-1" onsubmit="return confirm('Inativar esta loja?');">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="_action" value="delete_store">
                                        <input type="hidden" name="store_id" value="<?= $sid ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">Inativar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php else: ?>
                        <td class="text-white"><?= htmlspecialchars((string) ($st['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars((string) ($st['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $sst === 1 ? '<span class="badge badge-lumis badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
