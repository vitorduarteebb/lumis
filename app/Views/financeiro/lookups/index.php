<?php

declare(strict_types=1);

$types = is_array($types ?? null) ? $types : [];
$currentType = (string) ($currentType ?? 'finance_category');
$rows = $rows ?? [];
$basePath = (string) ($basePath ?? '/financeiro/opcoes-auxiliares');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Financeiro</div>
        <h2 class="h4 mb-1 text-white">Opções auxiliares</h2>
        <div class="text-secondary small">Categorias, centros de custo e formas de pagamento para classificação financeira.</div>
    </div>
    <?php if (can('financeiro.opcoes_auxiliares.view')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="/financeiro/opcoes-auxiliares/novo?type=<?= htmlspecialchars(rawurlencode($currentType), ENT_QUOTES, 'UTF-8') ?>">Novo item</a>
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
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Buscar nome…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Slug</th>
                <th>Ordem</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="5" class="text-secondary small py-4">Nenhum item nesta lista.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php $rid = (int) ($row['id'] ?? 0); $st = (int) ($row['status'] ?? 0); ?>
                <tr>
                    <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small font-monospace"><?= htmlspecialchars((string) ($row['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= (int) ($row['sort_order'] ?? 0) ?></td>
                    <td><?= $st === 1 ? '<span class="badge badge-lumis badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                    <td class="text-end">
                        <?php if (can('financeiro.opcoes_auxiliares.view')): ?>
                            <a class="btn btn-sm btn-lumis-secondary" href="/financeiro/opcoes-auxiliares/<?= $rid ?>/editar">Editar</a>
                            <form method="post" action="/financeiro/opcoes-auxiliares/<?= $rid ?>/excluir" class="d-inline" onsubmit="return confirm('Excluir este item?');">
                                <?= \App\Helpers\Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>
