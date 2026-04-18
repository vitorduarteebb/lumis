<?php
declare(strict_types=1);
$types = is_array($types ?? null) ? $types : [];
$currentType = (string) ($currentType ?? 'contract_type');
$rows = $rows ?? [];
$basePath = (string) ($basePath ?? '/contratos/opcoes-auxiliares');
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Contratos</div>
        <h2 class="h4 mb-1 text-white">Opções auxiliares</h2>
    </div>
    <?php if (can('contratos.opcoes_auxiliares.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/novo?type=<?= htmlspecialchars(rawurlencode($currentType), ENT_QUOTES, 'UTF-8') ?>">Novo item</a>
    <?php endif; ?>
</div>
<ul class="nav nav-pills gap-2 mb-3 flex-wrap">
    <?php foreach ($types as $slug => $label): ?>
        <li class="nav-item">
            <a class="nav-link <?= $currentType === $slug ? 'active' : '' ?> rounded-3" href="<?= htmlspecialchars($basePath . '?type=' . rawurlencode($slug), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
        </li>
    <?php endforeach; ?>
</ul>
<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <input type="hidden" name="type" value="<?= htmlspecialchars($currentType, ENT_QUOTES, 'UTF-8') ?>">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 360px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search"></i></span>
        <input type="search" name="q" class="form-control app-input" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>
<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead><tr><th>Nome</th><th>Slug</th><th>Aux.</th><th>Ordem</th><th>Status</th><th class="text-end">Ações</th></tr></thead>
        <tbody>
            <?php if ($rows === []): ?><tr><td colspan="6" class="text-secondary py-4">Nenhum item.</td></tr><?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php $rid = (int) ($row['id'] ?? 0); $st = (int) ($row['status'] ?? 0); ?>
                <tr>
                    <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small font-monospace"><?= htmlspecialchars((string) ($row['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars(mb_substr((string) ($row['value_text'] ?? ''), 0, 80), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) ($row['sort_order'] ?? 0) ?></td>
                    <td><?= $st === 1 ? '<span class="badge badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                    <td class="text-end">
                        <?php if (can('contratos.opcoes_auxiliares.edit')): ?><a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/<?= $rid ?>/editar">Editar</a><?php endif; ?>
                        <?php if (can('contratos.opcoes_auxiliares.delete')): ?>
                            <form method="post" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/<?= $rid ?>/excluir" class="d-inline" onsubmit="return confirm('Excluir?');"><?= \App\Helpers\Csrf::field() ?><button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>
