<?php
$rows = $rows ?? [];
$basePath = (string) ($basePath ?? '/cadastros/funcionarios');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
?>
<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Cadastros</div>
        <h2 class="h4 mb-1 text-white">Funcionários</h2>
        <div class="text-secondary small">Colaboradores, cargo e contatos.</div>
    </div>
    <?php if (can('cadastros.funcionarios.create') || can('employees.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="/cadastros/funcionarios/novo">Novo funcionário</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 360px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Nome, documento, e-mail, cargo…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <select name="status" class="form-select app-input" style="max-width: 160px;">
        <option value="">Status (todos)</option>
        <option value="1" <?= ($statusFilter ?? null) === '1' ? 'selected' : '' ?>>Ativo</option>
        <option value="0" <?= ($statusFilter ?? null) === '0' ? 'selected' : '' ?>>Inativo</option>
    </select>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Cargo</th>
                <th>Contato</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="5" class="text-secondary small py-4">Nenhum registro.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php $rid = (int) ($row['id'] ?? 0); $st = (int) ($row['status'] ?? 0); ?>
                <tr>
                    <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['job_title'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars(trim((string) ($row['email'] ?? '') . ' ' . (string) ($row['phone'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $st === 1 ? '<span class="badge badge-lumis badge-lumis-success">Ativo</span>' : '<span class="badge text-bg-secondary">Inativo</span>' ?></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-lumis-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Ações</button>
                            <ul class="dropdown-menu dropdown-menu-end app-dropdown border-0">
                                <li><a class="dropdown-item" href="/cadastros/funcionarios/<?= $rid ?>">Ver</a></li>
                                <?php if (can('cadastros.funcionarios.edit') || can('employees.edit')): ?>
                                    <li><a class="dropdown-item" href="/cadastros/funcionarios/<?= $rid ?>/editar">Editar</a></li>
                                <?php endif; ?>
                                <?php if (can('cadastros.funcionarios.delete') || can('employees.delete')): ?>
                                    <li>
                                        <form method="post" action="/cadastros/funcionarios/<?= $rid ?>/excluir" class="m-0" onsubmit="return confirm('Excluir?');">
                                            <?= \App\Helpers\Csrf::field() ?>
                                            <button type="submit" class="dropdown-item text-danger">Excluir</button>
                                        </form>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>
