<?php

declare(strict_types=1);

$rows = is_array($rows ?? null) ? $rows : [];
$status = (string) ($status ?? 'all');
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$total = (int) ($total ?? 0);
$basePath = (string) ($basePath ?? '/estoque/transferencias');
$queryParams = is_array($queryParams ?? null) ? $queryParams : [];

$stLab = ['pending' => 'Pendente', 'done' => 'Concluída', 'cancelled' => 'Cancelada'];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Estoque</div>
        <h2 class="h4 mb-1 text-white">Transferências</h2>
        <div class="text-secondary small">Entre lojas; conclua quando a mercadoria sair da origem.</div>
    </div>
    <?php if (can('estoque.transferencias.create')): ?>
        <a class="btn btn-primary btn-sm rounded-3" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/novo">Nova transferência</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end">
    <select name="status" class="form-select app-input" style="max-width: 200px;">
        <option value="all">Status (todos)</option>
        <?php foreach (['pending', 'done', 'cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= htmlspecialchars($stLab[$s] ?? $s, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Data</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="text-secondary small py-4">Nenhuma transferência.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $rid = (int) ($row['id'] ?? 0);
                $st = (string) ($row['status'] ?? '');
                ?>
                <tr>
                    <td class="font-monospace small"><?= $rid ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['from_store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['to_store_name'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if ($st === 'done'): ?>
                            <span class="badge badge-lumis-success"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php elseif ($st === 'cancelled'): ?>
                            <span class="badge text-bg-secondary"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php else: ?>
                            <span class="badge badge-lumis-warning"><?= htmlspecialchars($stLab[$st] ?? $st, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-lumis-secondary" href="<?= htmlspecialchars($basePath . '/' . $rid, ENT_QUOTES, 'UTF-8') ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>
