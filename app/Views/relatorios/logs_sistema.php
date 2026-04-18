<?php

declare(strict_types=1);

$rows = is_array($rows ?? null) ? $rows : [];
$userOpts = is_array($userOpts ?? null) ? $userOpts : [];
?>

<div class="lumis-page-head">
    <div>
        <div class="lumis-page-head__meta">Relatórios</div>
        <h2 class="h4 mb-1 text-white">Logs do sistema</h2>
        <div class="text-secondary small">Trilha de auditoria limitada aos usuários vinculados à empresa.</div>
    </div>
</div>

<form method="get" action="<?= htmlspecialchars((string) ($basePath ?? '/relatorios/logs-sistema'), ENT_QUOTES, 'UTF-8') ?>" class="lumis-toolbar mb-3 d-flex flex-wrap gap-2 align-items-end" data-export-ready="1">
    <select name="user_id" class="form-select app-input" style="max-width: 240px;">
        <option value="0">Usuário (todos)</option>
        <?php foreach ($userOpts as $u): ?>
            <option value="<?= (int) ($u['id'] ?? 0) ?>" <?= (int) ($userId ?? 0) === (int) ($u['id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($u['name'] ?? '') . ' — ' . (string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="action" class="form-control app-input" style="max-width: 140px;" placeholder="Ação" value="<?= htmlspecialchars((string) ($action ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="text" name="module" class="form-control app-input" style="max-width: 140px;" placeholder="Módulo" value="<?= htmlspecialchars((string) ($module ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_from" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateFrom ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="date" name="date_to" class="form-control app-input" style="max-width: 150px;" value="<?= htmlspecialchars((string) ($dateTo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <div class="input-group lumis-search flex-grow-1" style="max-width: 280px;">
        <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search" aria-hidden="true"></i></span>
        <input type="search" name="q" class="form-control app-input" placeholder="Descrição, texto livre…" value="<?= htmlspecialchars((string) ($search ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-lumis-secondary">Filtrar</button>
</form>

<div class="lumis-table-wrap mb-2" data-table="audit_logs">
    <table class="table lumis-table mb-0">
        <thead>
            <tr>
                <th>Data</th>
                <th>Usuário</th>
                <th>Ação</th>
                <th>Módulo</th>
                <th>IP</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
                <tr><td colspan="6" class="text-secondary small py-4">Nenhum log encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="text-secondary small text-nowrap"><?= htmlspecialchars((string) ($row['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-white small"><?= htmlspecialchars((string) ($row['user_name'] ?? $row['user_email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="font-monospace small"><?= htmlspecialchars((string) ($row['action'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['module'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary font-monospace small"><?= htmlspecialchars((string) ($row['ip_address'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-secondary small"><?= htmlspecialchars((string) ($row['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include base_path('app/Views/partials/pagination.php'); ?>
