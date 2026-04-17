<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
/** @var int $total */
/** @var int $page */
/** @var int $totalPages */
/** @var string $search */
/** @var string $basePath */
/** @var array<string, string|int|float|bool|null> $queryParams */

$rows = $rows ?? [];
$total = (int) ($total ?? 0);
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$search = (string) ($search ?? '');
$basePath = (string) ($basePath ?? '/configuracoes/usuarios');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Configurações</div>
        <h2 class="h4 mb-1 text-white">Usuários</h2>
        <div class="text-secondary small">Gerencie contas, papéis e acessos ao sistema.</div>
    </div>
    <?php if (can('configuracoes.usuarios.create') || can('users.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="/configuracoes/usuarios/novo">
            <i class="bi bi-person-plus me-1" aria-hidden="true"></i> Novo usuário
        </a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3">
    <div class="lumis-toolbar__left flex-grow-1">
        <div class="input-group lumis-search" style="max-width: 420px;">
            <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
            <input type="search" name="q" class="form-control app-input" placeholder="Buscar por nome ou e-mail…" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <button type="submit" class="btn btn-sm btn-lumis-secondary">Buscar</button>
    </div>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Empresa / Loja</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr>
                    <td colspan="5" class="text-secondary small py-4">Nenhum usuário encontrado.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $st = (int) ($row['status'] ?? 0);
                $rid = (int) ($row['id'] ?? 0);
                ?>
                <tr>
                    <td class="text-white"><?= htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small">
                        <?= htmlspecialchars((string) ($row['company_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!empty($row['store_name'])): ?>
                            <span class="text-secondary"> · </span><?= htmlspecialchars((string) $row['store_name'], ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($st === 1): ?>
                            <span class="badge badge-lumis badge-lumis-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-lumis-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Ações</button>
                            <ul class="dropdown-menu dropdown-menu-end app-dropdown border-0">
                                <li><a class="dropdown-item" href="/configuracoes/usuarios/<?= $rid ?>">Ver</a></li>
                                <?php if (can('configuracoes.usuarios.edit') || can('users.edit')): ?>
                                    <li><a class="dropdown-item" href="/configuracoes/usuarios/<?= $rid ?>/editar">Editar</a></li>
                                <?php endif; ?>
                                <?php if ((can('configuracoes.usuarios.delete') || can('users.edit')) && $rid !== auth_id()): ?>
                                    <li>
                                        <form method="post" action="/configuracoes/usuarios/<?= $rid ?>/excluir" class="m-0" onsubmit="return confirm('Desativar este usuário?');">
                                            <?= \App\Helpers\Csrf::field() ?>
                                            <button type="submit" class="dropdown-item text-danger">Excluir (lógico)</button>
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

<?php
include base_path('app/Views/partials/pagination.php');
?>
